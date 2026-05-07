# Implementation Plan

## Phase 1: Exploration Tests (BEFORE Fix)

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - SumUp Active Gateway Not Used in Event Checkout
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  - Create test in `tests/Feature/EventReservation/SumUpGatewayBugTest.php`
  - Test that when a seller has SumUp configured as active gateway (`gateway_accounts.provider = 'sumup'`, `enabled = true`) and creates a paid event, the system should:
    - Detect SumUp as the active gateway
    - Create Order with `gateway = 'sumup'`
    - Call `SumUpService::createCheckout()` instead of `MercadoPagoService::createPreference()`
    - Render view with SumUp checkout data (`checkout_id`, `sumupPublicKey`)
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found:
    - Order created with `gateway = 'mercadopago'` instead of `'sumup'`
    - `MercadoPagoService::createPreference()` called instead of `SumUpService::createCheckout()`
    - View rendered with Mercado Pago data instead of SumUp data
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Mercado Pago and Free Events Unchanged
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs
  - Create test in `tests/Feature/EventReservation/PreservationTest.php`
  - Write property-based tests capturing observed behavior patterns:
    - **Mercado Pago Active**: When seller has Mercado Pago as active gateway, observe that Order is created with `gateway = 'mercadopago'`, `MercadoPagoService::createPreference()` is called, and view receives `preferenceId` and `publicKey`
    - **Free Events**: When event has `effective_price = 0`, observe that Order is created with `gateway = 'free'` and is settled immediately
    - **Coupon Application**: Observe that coupon discounts are applied correctly
    - **Platform Fee Calculation**: Observe that platform fees are calculated correctly
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

## Phase 2: Implementation

- [x] 3. Implement gateway detection and SumUp support in EventReservationController

  - [x] 3.1 Add unified active gateway detection method to GatewayAccount model
    - Open `app/Models/GatewayAccount.php`
    - Add new method `public static function resolveActiveGatewayForSeller(int $sellerId): array`
    - Method should:
      - Query `gateway_accounts` WHERE `user_id = $sellerId` AND `enabled = true`
      - Return array: `['provider' => 'sumup'|'mercadopago'|null, 'enabled' => bool, 'config' => array, 'source' => 'seller'|'global']`
      - Prioritize seller credentials over global credentials
      - Return `null` for provider if no gateway is active
    - Keep existing methods `resolveForSeller()` and `resolveForSellerSumUp()` for compatibility
    - _Bug_Condition: isBugCondition(input) where input.event.effective_price > 0 AND sellerHasActiveGateway(input.seller, 'sumup')_
    - _Expected_Behavior: System detects active gateway and returns correct provider ('sumup' or 'mercadopago')_
    - _Preservation: Existing methods continue working for other checkout flows_
    - _Requirements: 2.1, 2.8, 3.3, 3.7_

  - [x] 3.2 Modify checkout() method to detect active gateway dynamically
    - Open `app/Http/Controllers/EventReservationController.php`
    - In `checkout()` method, replace `GatewayAccount::resolveForSeller()` with `GatewayAccount::resolveActiveGatewayForSeller()`
    - Detect active gateway: `$activeGateway = $gatewayConfig['provider']`
    - Pass `$activeGateway` to view instead of `$preferredGateway`
    - Verify at least one gateway is active (Mercado Pago OR SumUp) instead of only checking `$mpEnabled`
    - _Bug_Condition: System currently only checks Mercado Pago, ignoring SumUp_
    - _Expected_Behavior: System checks for any active gateway (Mercado Pago OR SumUp)_
    - _Preservation: Mercado Pago detection continues working_
    - _Requirements: 2.1, 2.5, 2.7, 3.1_

  - [x] 3.3 Modify reserve() method to use dynamic gateway detection
    - In `reserve()` method, replace hardcoded `$gatewayProvider = 'mercadopago'` with dynamic detection
    - Use `GatewayAccount::resolveActiveGatewayForSeller($event->user_id)` to get active gateway
    - Set `$gatewayProvider` based on detected active gateway
    - Create Order with dynamic `gateway` field based on active gateway
    - Add dependency injection: `SumUpService $sumUpService` parameter alongside `MercadoPagoService $mpService`
    - _Bug_Condition: System currently hardcodes 'mercadopago' without checking seller configuration_
    - _Expected_Behavior: System detects and uses seller's active gateway_
    - _Preservation: Mercado Pago flow continues working when it's the active gateway_
    - _Requirements: 2.2, 2.3, 2.4, 3.1_

  - [x] 3.4 Create processSumUpPayment() private method
    - Extract SumUp payment processing logic to new private method
    - Method signature: `private function processSumUpPayment(Order $order, Event $event, array $gatewayConfig, SumUpService $sumUpService)`
    - Call `$sumUpService->createCheckout($order, $options)` with appropriate options
    - Save `checkout_id` and `webhook_token` in Order metadata
    - Resolve SumUp `publicKey` (seller credentials or global)
    - Return view `checkout.transparent` with SumUp data: `checkout_id`, `sumupPublicKey`, `order`
    - _Bug_Condition: This method doesn't exist, preventing SumUp usage_
    - _Expected_Behavior: SumUp checkout is created and view receives correct data_
    - _Preservation: N/A (new functionality)_
    - _Requirements: 2.4, 2.6_

  - [x] 3.5 Refactor Mercado Pago logic to processMercadoPagoPayment() method
    - Extract existing Mercado Pago logic to new private method for symmetry
    - Method signature: `private function processMercadoPagoPayment(Order $order, Event $event, array $gatewayConfig, MercadoPagoService $mpService)`
    - Move existing `$mpService->createPreference()` call and related logic
    - Return view `checkout.transparent` with Mercado Pago data: `preferenceId`, `publicKey`, `order`
    - _Bug_Condition: N/A (refactoring for maintainability)_
    - _Expected_Behavior: Same behavior as before, just organized in separate method_
    - _Preservation: Mercado Pago flow must work exactly as before_
    - _Requirements: 3.1, 3.6_

  - [x] 3.6 Add conditional branch in reserve() to route to correct payment processor
    - After detecting active gateway, add conditional logic:
      - `if ($gatewayProvider === 'sumup') { return $this->processSumUpPayment(...); }`
      - `if ($gatewayProvider === 'mercadopago') { return $this->processMercadoPagoPayment(...); }`
      - `if ($gatewayProvider === 'free') { /* existing free event logic */ }`
    - Ensure all branches return appropriate response
    - _Bug_Condition: System currently has no branch for SumUp_
    - _Expected_Behavior: System routes to correct payment processor based on active gateway_
    - _Preservation: Mercado Pago and free event branches work as before_
    - _Requirements: 2.2, 2.4, 3.1, 3.2_

  - [x] 3.7 Update checkout.transparent view to support both gateways
    - Open `resources/views/checkout/transparent.blade.php`
    - Add gateway detection: check `$order->gateway` value
    - If `$order->gateway === 'sumup'`: render SumUp form with `checkout_id` and load SumUp.js
    - If `$order->gateway === 'mercadopago'`: render existing Mercado Pago form with `preferenceId`
    - Add `@include('partials.checkout.sumup-card-form')` when SumUp is active
    - _Bug_Condition: View currently only supports Mercado Pago_
    - _Expected_Behavior: View renders correct form based on gateway_
    - _Preservation: Mercado Pago rendering unchanged_
    - _Requirements: 2.6, 3.6_

  - [x] 3.8 Create SumUp card form partial
    - Create new file `resources/views/partials/checkout/sumup-card-form.blade.php`
    - Load SumUp.js SDK
    - Render secure iframe for card capture
    - Implement card tokenization via SumUp.js
    - Send `card_token` to backend after tokenization
    - Handle loading states and errors
    - _Bug_Condition: This partial doesn't exist_
    - _Expected_Behavior: SumUp card form renders and tokenizes cards_
    - _Preservation: N/A (new file)_
    - _Requirements: 2.6_

  - [x] 3.9 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - SumUp Active Gateway Used in Event Checkout
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - Verify that:
      - Order is created with `gateway = 'sumup'`
      - `SumUpService::createCheckout()` is called
      - View receives SumUp checkout data
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.6_

  - [x] 3.10 Verify preservation tests still pass
    - **Property 2: Preservation** - Mercado Pago and Free Events Unchanged
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix:
      - Mercado Pago events work exactly as before
      - Free events work exactly as before
      - Coupon application unchanged
      - Platform fee calculation unchanged
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

## Phase 3: Admin Panel Gateway Configuration Separation

- [x] 4. Separate gateway configurations into different tabs in admin panel

  - [x] 4.1 Create tabbed interface for gateway settings
    - Open admin gateway settings view (likely `resources/views/admin/settings/gateways.blade.php` or similar)
    - Implement tab navigation with two tabs:
      - "Mercado Pago" tab
      - "SumUp" tab
    - Use Bootstrap tabs or similar UI component for clean separation
    - Each tab should contain only the configuration fields for its respective gateway
    - Add visual indicator showing which gateway is currently active (enabled)
    - _Rationale: Prevents confusion by clearly separating different gateway configurations_
    - _Requirements: Additional requirement from user_

  - [x] 4.2 Move Mercado Pago settings to dedicated tab
    - Move all Mercado Pago configuration fields to "Mercado Pago" tab:
      - Public Key
      - Access Token
      - Webhook Secret
      - Enable/Disable toggle
      - Test mode toggle (if applicable)
    - Ensure form submission works correctly from tab
    - _Rationale: Clear organization of Mercado Pago specific settings_
    - _Requirements: Additional requirement from user_

  - [x] 4.3 Move SumUp settings to dedicated tab
    - Move all SumUp configuration fields to "SumUp" tab:
      - API Key
      - Merchant Code
      - Webhook Token
      - Enable/Disable toggle
      - Test mode toggle (if applicable)
    - Ensure form submission works correctly from tab
    - _Rationale: Clear organization of SumUp specific settings_
    - _Requirements: Additional requirement from user_

  - [x] 4.4 Add validation to ensure only one gateway is active
    - Add client-side validation: when enabling one gateway, show warning if another is already active
    - Add server-side validation in gateway settings controller
    - When enabling a gateway, automatically disable the other gateway
    - Show clear message to user: "Only one payment gateway can be active at a time. Enabling [Gateway A] will disable [Gateway B]."
    - _Rationale: Enforces business rule that only one gateway can be active per seller_
    - _Requirements: Additional requirement from user, aligns with business rule_

  - [x] 4.5 Update admin gateway settings controller
    - Locate and open the controller handling gateway settings (likely `app/Http/Controllers/Admin/GatewaySettingsController.php` or similar)
    - Update save/update methods to handle tabbed form submission
    - Implement logic to disable other gateways when one is enabled
    - Add validation rules ensuring only one gateway has `enabled = true`
    - Return appropriate success/error messages
    - _Rationale: Backend enforcement of single active gateway rule_
    - _Requirements: Additional requirement from user_

  - [x] 4.6 Add visual feedback for active gateway
    - Add badge or indicator on each tab showing "Active" or "Inactive" status
    - Use color coding: green for active, gray for inactive
    - Update indicator dynamically when gateway status changes
    - Consider adding icon (checkmark for active, x for inactive)
    - _Rationale: Immediate visual feedback of which gateway is currently active_
    - _Requirements: Additional requirement from user_

## Phase 4: Final Validation

- [x] 5. Checkpoint - Ensure all tests pass and manual testing
  - Run full test suite: `php artisan test`
  - Verify all bug condition tests pass
  - Verify all preservation tests pass
  - Manual testing checklist:
    - Create test seller with SumUp active, create paid event, verify checkout uses SumUp
    - Create test seller with Mercado Pago active, create paid event, verify checkout uses Mercado Pago
    - Create free event, verify immediate settlement without gateway
    - Test gateway switching in admin panel (Mercado Pago → SumUp → Mercado Pago)
    - Verify only one gateway can be active at a time in admin panel
    - Verify tab interface is clear and prevents configuration confusion
  - If any issues arise, document and ask user for guidance
  - _Requirements: All requirements validation_
