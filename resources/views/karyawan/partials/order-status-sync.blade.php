<script>
(function () {
    const progressMap = {
        arrived_at_laundry: 1,
        washing: 2,
        drying_ironing: 3,
        packing: 4,
        ready_for_delivery: 5,
        delivering: 6,
        completed: 7,
    };

    const employeeEditableSteps = [
        'arrived_at_laundry',
        'washing',
        'drying_ironing',
        'packing',
        'ready_for_delivery',
    ];

    window.dispatchKaryawanOrderStatusUpdate = function (data) {
        const payload = {
            order_id: data.order?.id ?? data.order_id,
            status: data.order?.status ?? data.status,
            order_code: data.order?.order_code ?? data.order_code,
            counts: data.counts ?? null,
        };

        try {
            localStorage.setItem('karyawan-order-sync', JSON.stringify({ ...payload, ts: Date.now() }));
        } catch (e) {}

        window.dispatchEvent(new CustomEvent('karyawan-order-status-updated', { detail: payload }));
    };

    window.updateKaryawanOrderProgressRow = function (orderId, status) {
        const row = document.getElementById('order-row-' + orderId);
        if (!row) return;

        row.dataset.orderStatus = status;

        const currentStep = progressMap[status] ?? 0;
        const steps = row.querySelectorAll('[data-progress-step]');

        steps.forEach((stepEl) => {
            const stepStatus = stepEl.dataset.progressStep;
            const stepIndex = parseInt(stepEl.dataset.progressIndex, 10);
            const isDone = currentStep > stepIndex;
            const isActive = currentStep === stepIndex;
            const isEditable = employeeEditableSteps.includes(stepStatus);

            const btn = stepEl.querySelector('button, span.w-6');
            if (!btn) return;

            btn.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black transition-all relative z-30 ' +
                (isDone ? 'bg-emerald-500 text-white' :
                    (isActive ? 'bg-blue-600 text-white ring-2 ring-blue-200' :
                        (isEditable ? 'bg-white border border-gray-200 text-gray-400 hover:bg-gray-50 hover:scale-125 hover:shadow-lg' :
                            'bg-white border border-gray-200 text-gray-300 cursor-not-allowed')));

            if (isEditable) {
                btn.classList.add('hover:scale-125', 'hover:shadow-lg');
            }

            btn.textContent = isDone ? '✓' : stepIndex;

            const label = stepEl.querySelector('[data-progress-label]');
            if (label) {
                label.className = 'text-[9px] font-black mt-2.5 uppercase tracking-wider text-center whitespace-nowrap leading-none ' +
                    (isActive ? 'text-blue-600 font-black scale-105' : (isDone ? 'text-emerald-600' : 'text-gray-400'));
            }

            const container = stepEl.querySelector('[data-status-updated-container]');
            if (container) {
                if (isActive) {
                    container.innerHTML = `
                        <div class="inline-flex items-center gap-0.5 px-1 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[8px] font-bold rounded-md scale-90 whitespace-nowrap transition-all duration-300">
                            <span>Status Updated</span>
                        </div>
                    `;
                    setTimeout(() => {
                        const badge = container.querySelector('div');
                        if (badge) {
                            badge.style.transition = 'all 0.3s ease';
                            badge.style.opacity = '0';
                            badge.style.transform = 'scale(0.9)';
                            setTimeout(() => { badge.remove(); }, 300);
                        }
                    }, 5000);
                } else {
                    container.innerHTML = '';
                }
            }
        });

        const overlay = row.querySelector('[data-progress-line]');
        if (overlay) {
            overlay.style.width = 'calc(' + (currentStep > 1 ? ((currentStep - 1) / 6) * 100 : 0) + '%)';
        }

        const badge = row.querySelector('[data-order-status-badge]');
        if (badge) {
            badge.textContent = status.replace(/_/g, ' ');
        }
    };

    window.applyKaryawanPipelineCounts = function (counts) {
        if (!counts) return;

        const map = {
            arrived_at_laundry: '.pipeline-arrived-count',
            washing: '.pipeline-washing-count',
            drying_ironing: '.pipeline-ironing-count',
            packing: '.pipeline-packing-count',
            ready_for_delivery: '.pipeline-ready-count',
        };

        Object.entries(map).forEach(([key, selector]) => {
            document.querySelectorAll(selector).forEach((el) => {
                if (counts[key] !== undefined) {
                    el.textContent = Number(counts[key]).toLocaleString();
                }
            });
        });

        Object.entries({
            arrived_at_laundry: '.arrived-laundry-stat',
            washing: '.pipeline-washing-count',
            ready_for_delivery: '.ready-delivery-stat',
        }).forEach(([key, selector]) => {
            document.querySelectorAll(selector).forEach((el) => {
                if (counts[key] !== undefined) {
                    el.textContent = Number(counts[key]).toLocaleString();
                }
            });
        });
    };

    window.addEventListener('karyawan-order-status-updated', function (e) {
        const { order_id, status, counts } = e.detail || {};
        if (order_id && status) {
            window.updateKaryawanOrderProgressRow(order_id, status);
        }
        window.applyKaryawanPipelineCounts(counts);
    });

    window.addEventListener('storage', function (e) {
        if (e.key !== 'karyawan-order-sync' || !e.newValue) return;
        try {
            const data = JSON.parse(e.newValue);
            window.dispatchEvent(new CustomEvent('karyawan-order-status-updated', { detail: data }));
        } catch (err) {}
    });

    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('[data-karyawan-status-form]');
        if (!form) return;

        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const message = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to update order status.');
                throw new Error(message);
            }

            window.dispatchKaryawanOrderStatusUpdate(data);

            const successMsg = data.message || 'Order status updated successfully.';
            document.dispatchEvent(new CustomEvent('karyawan-order-status-success', {
                detail: { message: successMsg },
                bubbles: true,
            }));
            window.dispatchEvent(new CustomEvent('karyawan-order-status-success', {
                detail: { message: successMsg },
            }));
        } catch (err) {
            alert(err.message || 'Failed to update order status.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    if (window.Echo) {
        window.Echo.private('karyawan.orders')
            .listen('.OrderStatusUpdated', (payload) => {
                window.dispatchKaryawanOrderStatusUpdate(payload);
            });
    }
})();
</script>
