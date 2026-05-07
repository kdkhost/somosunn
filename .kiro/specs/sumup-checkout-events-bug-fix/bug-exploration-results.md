# Bug Condition Exploration Results

## Test Execution Summary

**Test File**: `tests/Feature/EventReservation/SumUpGatewayBugTest.php`  
**Test Method**: `test_seller_with_sumup_active_gateway_should_use_sumup_for_event_checkout`  
**Execution Date**: 2025-01-XX  
**Status**: ✅ **BUG CONFIRMED** (Test failed as expected on unfixed code)

## Counterexample Found

### Scenario
- **Seller**: Has SumUp configured as active gateway (`provider='sumup'`, `enabled=true`)
- **Event**: Paid event with price = R$ 100.00
- **Buyer**: Attempts to reserve a ticket for the event

### Expected Behavior
1. System should detect SumUp as the active gateway
2. Create Order with `gateway = 'sumup'`
3. Call `SumUpService::createCheckout()` to create SumUp checkout
4. Render view with SumUp checkout data (`checkout_id`, `sumupPublicKey`)

### Actual Behavior (Bug Manifestation)
**System redirected with error message:**
```
"Pagamento indisponível: o organizador ainda não configurou um método de pagamento."
```

**HTTP Status**: 302 (Redirect)  
**Redirect Target**: Event show page  
**Session Error**: Payment unavailable - organizer hasn't configured a payment method

### Root Cause Analysis

The bug is located in `app/Http/Controllers/EventReservationController.php`:

1. **Line ~119**: `$paymentsConfigured = $gateways['mpEnabled'];`
   - System only checks if Mercado Pago is enabled
   - Doesn't check for SumUp or any other gateway
   - Returns `false` when seller has only SumUp configured

2. **Line ~127**: `$gatewayProvider = 'mercadopago';`
   - Gateway provider is hardcoded to 'mercadopago'
   - No logic to detect which gateway the seller actually configured

3. **Line ~640**: `$mpService->createPreference()`
   - Always calls Mercado Pago service
   - No conditional branch to call `SumUpService::createCheckout()` when SumUp is active

4. **Line ~48** (checkout method): `$gateways = \App\Models\GatewayAccount::resolveForSeller($seller ? (int) $seller->id : 0);`
   - Uses `resolveForSeller()` which only returns Mercado Pago information
   - Doesn't use `resolveForSellerSumUp()` or a unified method

### Evidence

1. ✅ Seller has SumUp configured as active gateway in `gateway_accounts` table
2. ✅ Event is paid (effective_price > 0)
3. ✅ System rejected the checkout with error "no payment method configured"
4. ✅ Error occurs because system only checks `mpEnabled` and ignores SumUp
5. ✅ Gateway provider is hardcoded to 'mercadopago' without detection logic

### Impact

**Affected Users**: All sellers who configured SumUp as their active payment gateway

**Business Impact**: 
- Sellers with SumUp cannot sell paid event tickets
- System incorrectly reports "no payment method configured" even when SumUp is properly set up
- Forces sellers to use Mercado Pago even if they prefer SumUp

**Severity**: HIGH - Completely blocks SumUp usage for event checkouts

## Conclusion

The bug condition exploration test successfully demonstrated that the bug exists in the unfixed code. The system does not detect SumUp as a valid gateway for event checkouts and always assumes Mercado Pago is the only option.

The test will pass (expected behavior will be satisfied) once the fix is implemented in Phase 2 of the implementation plan.

## Next Steps

1. ✅ Bug confirmed through exploration test
2. ⏭️ Proceed to Phase 2: Implement fix
   - Add `GatewayAccount::resolveActiveGatewayForSeller()` method
   - Modify `EventReservationController::checkout()` to detect active gateway
   - Modify `EventReservationController::reserve()` to use dynamic gateway detection
   - Create `processSumUpPayment()` method
   - Update view to support both gateways
3. ⏭️ Re-run exploration test to verify fix (test should pass after implementation)
