import { Head, useForm, usePage, router } from '@inertiajs/react';
import { useState, useMemo, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar } from '@/Layouts/AppLayout';
import Input from '@/Components/Input';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button from '@/Components/Button';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export default function MeterReadingInput({ period, reading, previousReading = null, nextPendingReading = null }) {
    const { flash, auth } = usePage().props;
    const customer = reading.customer;

    const { data, setData, patch, processing, errors, reset } = useForm({
        end_reading: reading.end_reading || '',
        notes: reading.notes || '',
    });

    // Calculate usage based on input
    const usage = useMemo(() => {
        const start = reading.start_reading || 0;
        const end = parseFloat(data.end_reading) || 0;
        if (end >= start) {
            return (end - start).toFixed(1);
        }
        return '-';
    }, [reading.start_reading, data.end_reading]);

    // Calculate estimated bill
    const estimatedBill = useMemo(() => {
        if (usage === '-' || parseFloat(usage) < 0) return 0;

        const usageValue = parseFloat(usage);
        const golongan = customer?.golongan;

        if (!golongan || !golongan.tariff_levels || golongan.tariff_levels.length === 0) {
            return Math.round(usageValue * 1000); // Default rate
        }

        let total = 0;
        let remaining = usageValue;
        const tariffs = golongan.tariff_levels || [];

        for (const tariff of tariffs) {
            if (remaining <= 0) break;

            const cap = tariff.meter_end !== null
                ? Math.max((tariff.meter_end - tariff.meter_start) + 1, 0)
                : null;

            const portion = cap ? Math.min(remaining, cap) : remaining;
            total += portion * parseFloat(tariff.price);
            remaining -= portion;
        }

        // If still remaining usage, use last tariff
        if (remaining > 0 && tariffs.length > 0) {
            const lastTariff = tariffs[tariffs.length - 1];
            total += remaining * parseFloat(lastTariff.price);
        }

        // Add non-air fees
        for (const fee of (golongan.non_air_fees || [])) {
            total += parseFloat(fee.price);
        }

        return Math.round(total);
    }, [usage, customer]);

    const handleSubmit = (e) => {
        e.preventDefault();
        patch(`/catat-meter/${period.id}/readings/${reading.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // After saving, redirect back to the period show page (which shows pending tab by default)
                // The user will see the pending list automatically
            }
        });
    };

    const prevMonthName = previousReading
        ? `${MONTHS[previousReading.period?.month]} ${previousReading.period?.year}`
        : 'Sebelumnya';

    return (
        <AppLayout>
            <Head title={`Catat Meter - ${customer?.name}`} />
            <PageContainer>
                <TopAppBar
                    title="Input Meter"
                    subtitle={`${MONTHS[period.month]} ${period.year}`}
                    backHref={`/catat-meter/${period.id}`}
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.status && <Alert type="success" className="mb-4">{flash.status}</Alert>}
                    {flash?.error && <Alert type="error" className="mb-4">{flash.error}</Alert>}

                    {/* Customer Info Card */}
                    <Card variant="filled" className="mb-4" style={{ backgroundColor: 'var(--md-sys-color-primary-container)', color: 'var(--md-sys-color-on-primary-container)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                            <div className="md-avatar lg" style={{ backgroundColor: 'var(--md-sys-color-primary)', color: 'var(--md-sys-color-on-primary)' }}>
                                {customer?.name?.charAt(0) || '?'}
                            </div>
                            <div style={{ flex: 1 }}>
                                <div className="md-title-large" style={{ fontWeight: 600 }}>{customer?.name}</div>
                                <div className="md-body-medium" style={{ opacity: 0.8 }}>{customer?.customer_code}</div>
                                <div className="md-body-small" style={{ opacity: 0.7, marginTop: '4px' }}>
                                    {customer?.address_short} • {customer?.golongan?.name}
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Customer Details Card */}
                    <Card variant="outlined" className="mb-4">
                        <div className="md-label-medium text-muted" style={{ marginBottom: '12px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Data Pelanggan
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                            <div>
                                <div className="md-label-small text-muted">Area</div>
                                <div className="md-body-medium">{customer?.area?.name || '-'}</div>
                            </div>
                            <div>
                                <div className="md-label-small text-muted">Golongan</div>
                                <div className="md-body-medium">{customer?.golongan?.name || '-'}</div>
                            </div>
                            <div>
                                <div className="md-label-small text-muted">Telepon</div>
                                <div className="md-body-medium">{customer?.phone_number || '-'}</div>
                            </div>
                            <div>
                                <div className="md-label-small text-muted">Jumlah KK</div>
                                <div className="md-body-medium">{customer?.family_members || '-'}</div>
                            </div>
                        </div>
                    </Card>

                    {/* Previous Reading Info */}
                    <Card variant="outlined" className="mb-4">
                        <div className="md-label-medium text-muted" style={{ marginBottom: '12px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Data {prevMonthName}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '16px', textAlign: 'center' }}>
                            <div>
                                <div className="md-headline-small" style={{ fontWeight: 600, color: 'var(--md-sys-color-primary)' }}>
                                    {previousReading?.end_reading || reading.start_reading || 0}
                                </div>
                                <div className="md-label-small" style={{ color: 'var(--md-sys-color-outline)' }}>
                                    Stand Akhir
                                </div>
                            </div>
                            <div>
                                <div className="md-headline-small" style={{ fontWeight: 600, color: 'var(--md-sys-color-tertiary)' }}>
                                    {previousReading?.usage_m3 || 0} m³
                                </div>
                                <div className="md-label-small" style={{ color: 'var(--md-sys-color-outline)' }}>
                                    Pemakaian
                                </div>
                            </div>
                            <div>
                                <div className="md-headline-small" style={{ fontWeight: 600, color: 'var(--md-sys-color-secondary)' }}>
                                    Rp {(previousReading?.bill_amount || 0).toLocaleString('id-ID')}
                                </div>
                                <div className="md-label-small" style={{ color: 'var(--md-sys-color-outline)' }}>
                                    Tagihan
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Input Form */}
                    <form onSubmit={handleSubmit} className="md-form-stack">
                        <Card variant="outlined" className="mb-4">
                            <div className="md-label-medium text-muted" style={{ marginBottom: '16px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                Input Bulan Ini
                            </div>

                            {/* Start Reading (readonly) */}
                            <div className="mb-4" style={{ display: 'flex', gap: '16px', alignItems: 'flex-end' }}>
                                <div style={{ flex: 1 }}>
                                    <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Stand Awal</div>
                                    <div className="md-headline-medium" style={{ fontWeight: 600, color: 'var(--md-sys-color-primary)' }}>
                                        {reading.start_reading || 0}
                                    </div>
                                </div>
                                <div style={{ padding: '0 8px' }}>
                                    <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-outline)' }}>arrow_forward</span>
                                </div>
                                <div style={{ flex: 1 }}>
                                    <Input
                                        label="Stand Akhir"
                                        icon="speed"
                                        type="number"
                                        step="0.1"
                                        min={reading.start_reading || 0}
                                        value={data.end_reading}
                                        onChange={(e) => setData('end_reading', e.target.value)}
                                        error={errors.end_reading}
                                        required
                                        autoFocus
                                        style={{ fontSize: '1.25rem', fontWeight: '600' }}
                                    />
                                </div>
                            </div>

                            {/* Usage Display */}
                            <div style={{
                                textAlign: 'center',
                                padding: '24px',
                                backgroundColor: usage !== '-' && parseFloat(usage) >= 0 ? 'var(--md-sys-color-success-container)' : 'var(--md-sys-color-surface-variant)',
                                borderRadius: '12px',
                                marginTop: '16px'
                            }}>
                                <div className="md-display-medium" style={{
                                    fontWeight: 700,
                                    color: usage !== '-' && parseFloat(usage) >= 0 ? 'var(--md-sys-color-on-success-container)' : 'var(--md-sys-color-outline)'
                                }}>
                                    {usage} m³
                                </div>
                                <div className="md-label-medium" style={{ marginTop: '4px', color: 'var(--md-sys-color-outline)' }}>
                                    Pemakaian Bulan Ini
                                </div>
                            </div>

                            {/* Estimated Bill */}
                            {usage !== '-' && parseFloat(usage) >= 0 && (
                                <div style={{
                                    textAlign: 'center',
                                    padding: '16px',
                                    backgroundColor: 'var(--md-sys-color-secondary-container)',
                                    borderRadius: '12px',
                                    marginTop: '12px'
                                }}>
                                    <div className="md-title-large" style={{
                                        fontWeight: 600,
                                        color: 'var(--md-sys-color-on-secondary-container)'
                                    }}>
                                        Rp {estimatedBill.toLocaleString('id-ID')}
                                    </div>
                                    <div className="md-label-small" style={{ color: 'var(--md-sys-color-on-secondary-container)', opacity: 0.8 }}>
                                        Estimasi Tagihan
                                    </div>
                                </div>
                            )}
                        </Card>

                        <Input
                            label="Catatan (Opsional)"
                            icon="notes"
                            type="text"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            error={errors.notes}
                            placeholder="Contoh: Meter rusak, estimasi, dll"
                        />

                        <Button
                            type="submit"
                            variant="filled"
                            fullWidth
                            loading={processing}
                            icon="save"
                            size="lg"
                        >
                            Simpan & Kembali
                        </Button>
                    </form>

                    {/* Quick Navigation */}
                    <div style={{ marginTop: '24px', textAlign: 'center' }}>
                        <Button
                            variant="text"
                            icon="format_list_numbered"
                            onClick={() => router.visit(`/catat-meter/${period.id}`)}
                        >
                            Kembali ke Daftar
                        </Button>
                    </div>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
