/**
 * SumUp Integration JavaScript
 * Integração do SumUp com o sistema de checkout
 */

class SumUpIntegration {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: '/api/v1/sumup',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            ...config
        };
        
        this.isInitialized = false;
        this.currentCheckout = null;
    }

    /**
     * Inicializa a integração do SumUp
     */
    async init() {
        if (this.isInitialized) {
            return;
        }

        try {
            // Verificar se o SumUp está disponível
            const availability = await this.checkAvailability();
            
            if (!availability.available) {
                console.log('SumUp não está disponível:', availability);
                return false;
            }

            this.isInitialized = true;
            console.log('SumUp inicializado com sucesso');
            return true;

        } catch (error) {
            console.error('Erro ao inicializar SumUp:', error);
            return false;
        }
    }

    /**
     * Verifica disponibilidade do SumUp
     */
    async checkAvailability(params = {}) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify(params)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();

        } catch (error) {
            console.error('Erro ao verificar disponibilidade do SumUp:', error);
            throw error;
        }
    }

    /**
     * Cria um checkout no SumUp
     */
    async createCheckout(checkoutData) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify(checkoutData)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            this.currentCheckout = result.data;
            return result;

        } catch (error) {
            console.error('Erro ao criar checkout SumUp:', error);
            throw error;
        }
    }

    /**
     * Consulta status de um checkout
     */
    async getCheckoutStatus(checkoutId) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/checkout/${checkoutId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();

        } catch (error) {
            console.error('Erro ao consultar status do checkout:', error);
            throw error;
        }
    }

    /**
     * Calcula parcelamento
     */
    async calculateInstallments(amount, installments) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/installments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({ amount, installments })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();

        } catch (error) {
            console.error('Erro ao calcular parcelamento:', error);
            throw error;
        }
    }

    /**
     * Processa pagamento via SumUp
     */
    async processPayment(paymentData) {
        try {
            // Criar checkout
            const checkout = await this.createCheckout(paymentData);
            
            if (!checkout.success) {
                throw new Error(checkout.message || 'Erro ao criar checkout');
            }

            // Redirecionar para URL de pagamento do SumUp
            if (checkout.data.checkout_url) {
                window.location.href = checkout.data.checkout_url;
                return checkout;
            }

            // Se não há URL de redirecionamento, retornar dados para processamento local
            return checkout;

        } catch (error) {
            console.error('Erro ao processar pagamento SumUp:', error);
            throw error;
        }
    }

    /**
     * Renderiza opções de pagamento SumUp
     */
    renderPaymentOptions(container, options = {}) {
        const containerElement = typeof container === 'string' 
            ? document.querySelector(container) 
            : container;

        if (!containerElement) {
            console.error('Container não encontrado:', container);
            return;
        }

        const config = { ...this.config, ...options };
        
        // HTML das opções de pagamento
        const html = `
            <div class="sumup-payment-options">
                <h3>Pagar com SumUp</h3>
                
                ${config.methods?.includes('card') ? `
                    <div class="payment-method" data-method="card">
                        <i class="fas fa-credit-card"></i>
                        <span>Cartão de Crédito</span>
                    </div>
                ` : ''}
                
                ${config.methods?.includes('pix') ? `
                    <div class="payment-method" data-method="pix">
                        <i class="fa-brands fa-pix"></i>
                        <span>PIX</span>
                    </div>
                ` : ''}
                
                ${config.installment_options ? `
                    <div class="installment-options">
                        <label>Parcelas:</label>
                        <select id="sumup-installments">
                            ${config.installment_options.map(option => `
                                <option value="${option.installments}">
                                    ${option.installments}x de R$ ${option.installment_amount.toFixed(2)}
                                    ${option.interest_amount > 0 ? ` (+ R$ ${option.interest_amount.toFixed(2)} juros)` : ''}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                ` : ''}
                
                <button id="sumup-pay-button" class="btn btn-primary">
                    Pagar com SumUp
                </button>
            </div>
        `;

        containerElement.innerHTML = html;

        // Adicionar event listeners
        this.attachEventListeners(containerElement, config);
    }

    /**
     * Adiciona event listeners aos elementos de pagamento
     */
    attachEventListeners(container, config) {
        const payButton = container.querySelector('#sumup-pay-button');
        const methodElements = container.querySelectorAll('.payment-method');
        const installmentSelect = container.querySelector('#sumup-installments');

        let selectedMethod = config.methods?.[0] || 'card';
        let selectedInstallments = 1;

        // Seleção de método de pagamento
        methodElements.forEach(element => {
            element.addEventListener('click', () => {
                methodElements.forEach(el => el.classList.remove('selected'));
                element.classList.add('selected');
                selectedMethod = element.dataset.method;
            });
        });

        // Seleção de parcelas
        if (installmentSelect) {
            installmentSelect.addEventListener('change', () => {
                selectedInstallments = parseInt(installmentSelect.value);
            });
        }

        // Botão de pagamento
        if (payButton) {
            payButton.addEventListener('click', async () => {
                try {
                    payButton.disabled = true;
                    payButton.textContent = 'Processando...';

                    const paymentData = {
                        ...config.paymentData,
                        payment_method: selectedMethod,
                        installments: selectedInstallments
                    };

                    await this.processPayment(paymentData);

                } catch (error) {
                    console.error('Erro no pagamento:', error);
                    alert('Erro ao processar pagamento: ' + error.message);
                } finally {
                    payButton.disabled = false;
                    payButton.textContent = 'Pagar com SumUp';
                }
            });
        }
    }

    /**
     * Obtém token de autenticação
     */
    getAuthToken() {
        // Implementar lógica para obter token de autenticação
        // Pode ser de localStorage, cookie, etc.
        return localStorage.getItem('auth_token') || '';
    }

    /**
     * Monitora status de pagamento
     */
    async monitorPaymentStatus(checkoutId, callback, maxAttempts = 30) {
        let attempts = 0;
        
        const checkStatus = async () => {
            try {
                attempts++;
                const status = await this.getCheckoutStatus(checkoutId);
                
                if (callback) {
                    const shouldContinue = callback(status, attempts);
                    if (!shouldContinue) {
                        return;
                    }
                }

                // Status finais
                if (['paid', 'failed', 'cancelled'].includes(status.data?.status)) {
                    return status;
                }

                // Continuar monitorando se não atingiu o limite
                if (attempts < maxAttempts) {
                    setTimeout(checkStatus, 2000); // Verificar a cada 2 segundos
                }

            } catch (error) {
                console.error('Erro ao monitorar status:', error);
                if (callback) {
                    callback({ error: error.message }, attempts);
                }
            }
        };

        checkStatus();
    }
}

// Exportar para uso global
window.SumUpIntegration = SumUpIntegration;

// Exemplo de uso
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar SumUp se houver configuração na página
    if (window.sumupConfig) {
        const sumup = new SumUpIntegration(window.sumupConfig);
        sumup.init().then(initialized => {
            if (initialized) {
                console.log('SumUp pronto para uso');
                window.sumupInstance = sumup;
            }
        });
    }
});