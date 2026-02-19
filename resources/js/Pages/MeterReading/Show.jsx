import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section, EmptyState } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button, { IconButton, FAB } from '@/Components/Button';
import { BottomSheet, ConfirmModal } from '@/Components/Modal';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export default function MeterPeriodShow({
    period,
    areas = [],
    activeAreaId,
    readings = [],
    pendingReadings = [],
    completedReadings = [],
    summary = {}
}) {
    const { flash, auth } = usePage().props;
    const [searchQuery, setSearchQuery] = useState('');
    const [showTab, setShowTab] = useState('pending'); // pending | completed | all
    const [showPaymentSheet, setShowPaymentSheet] = useState(false);
    const [selectedReading, setSelectedReading] = useState(null);
    const [showPublishConfirm, setShowPublishConfirm] = useState(false);
    const [publishTarget, setPublishTarget] = useState(null);
    const [showBillsSheet, setShowBillsSheet] = useState(false);
    const [customerBills, setCustomerBills] = useState([]);
    const [selectedBill, setSelectedBill] = useState(null);
    const [loadingBills, setLoadingBills] = useState(false);

    const paymentForm = useForm({
        amount: '',
        method: 'cash',
        reference_number: '',
        notes: '',
    });

    // Filter readings based on search and tab
    const filteredReadings = useMemo(() => {
        let list = readings;

        if (showTab === 'pending') {
            list = pendingReadings;
        } else if (showTab === 'completed') {
            list = completedReadings;
        }

        if (searchQuery.trim()) {
            const q = searchQuery.toLowerCase();
            list = list.filter(r =>
                r.customer?.name?.toLowerCase().includes(q) ||
                r.customer?.customer_code?.toLowerCase().includes(q) ||
                r.customer?.address_short?.toLowerCase().includes(q)
            );
        }

        return list;
    }, [readings, pendingReadings, completedReadings, showTab, searchQuery]);

    // Count published and paid readings
    const publishedCount = useMemo(() => {
        return completedReadings.filter(r => r.bill_published_at).length;
    }, [completedReadings]);

    const paidCount = useMemo(() => {
        return completedReadings.filter(r => r.bill?.status === 'paid').length;
    }, [completedReadings]);

    const handleAreaChange = (areaId) => {
        router.get(`/catat-meter/${period.id}`, { area: areaId }, { preserveScroll: true, preserveState: true });
    };

    const handlePublish = (reading) => {
        setPublishTarget(reading);
        setShowPublishConfirm(true);
    };

    const confirmPublish = () => {
        if (!publishTarget) return;
        router.post(`/billing/${publishTarget.id}/publish`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                setShowPublishConfirm(false);
                setPublishTarget(null);
            }
        });
    };

    const handleUnpublish = (reading) => {
        router.post(`/billing/${reading.id}/unpublish`, {}, { preserveScroll: true });
    };

    // Open bills selection sheet for a customer
    const openBillsSheet = async (reading) => {
        setSelectedReading(reading);
        setLoadingBills(true);
        setShowBillsSheet(true);

        try {
            // Fetch all active bills for this customer
            const response = await fetch(`/billing/customer/${reading.customer_id}/bills`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                const data = await response.json();
                setCustomerBills(data.bills || []);
            } else {
                // Fallback: use current reading's bill
                if (reading.bill) {
                    setCustomerBills([{
                        id: reading.bill.id,
                        period_label: `${period.month}/${period.year}`,
                        total_amount: reading.bill.total_amount || reading.bill_amount,
                        paid_amount: reading.bill.paid_amount || 0,
                        remaining: reading.bill.remaining || reading.bill.total_amount || reading.bill_amount,
                        usage_m3: reading.usage_m3,
                        status: reading.bill.status,
                    }]);
                } else {
                    setCustomerBills([]);
                }
            }
        } catch (error) {
            // Fallback
            if (reading.bill) {
                setCustomerBills([{
                    id: reading.bill.id,
                    period_label: `${period.month}/${period.year}`,
                    total_amount: reading.bill.total_amount || reading.bill_amount,
                    paid_amount: reading.bill.paid_amount || 0,
                    remaining: reading.bill.remaining || reading.bill.total_amount || reading.bill_amount,
                    usage_m3: reading.usage_m3,
                    status: reading.bill.status,
                }]);
            } else {
                setCustomerBills([]);
            }
        }

        setLoadingBills(false);
    };

    // Select a bill for payment
    const selectBillForPayment = (bill) => {
        setSelectedBill(bill);
        paymentForm.setData({
            amount: bill.remaining || '',
            method: 'cash',
            reference_number: '',
            notes: '',
        });
        setShowBillsSheet(false);
        setShowPaymentSheet(true);
    };

    // Direct payment for current bill
    const openPaymentSheet = (reading) => {
        setSelectedReading(reading);
        setSelectedBill({
            id: reading.bill?.id,
            period_label: `${period.month}/${period.year}`,
            total_amount: reading.bill?.total_amount || reading.bill_amount,
            paid_amount: reading.bill?.paid_amount || 0,
            remaining: reading.bill?.remaining || reading.bill?.total_amount || reading.bill_amount,
            usage_m3: reading.usage_m3,
            status: reading.bill?.status,
        });
        paymentForm.setData({
            amount: reading.bill?.remaining || reading.bill_amount || '',
            method: 'cash',
            reference_number: '',
            notes: '',
        });
        setShowPaymentSheet(true);
    };

    const submitPayment = (e) => {
        e.preventDefault();
        if (!selectedBill?.id) return;

        paymentForm.post(`/billing/${selectedBill.id}/pay`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowPaymentSheet(false);
                setSelectedReading(null);
                setSelectedBill(null);
            }
        });
    };

    const getStatusBadge = (reading) => {
        if (!reading.recorded_at) {
            return { label: 'Belum', color: 'var(--md-sys-color-error-container)', textColor: 'var(--md-sys-color-on-error-container)' };
        }
        if (!reading.bill_published_at) {
            return { label: 'Tercatat', color: 'var(--md-sys-color-tertiary-container)', textColor: 'var(--md-sys-color-on-tertiary-container)' };
        }
        if (reading.bill?.status === 'paid') {
            return { label: 'Lunas', color: 'var(--md-sys-color-success)', textColor: '#fff' };
        }
        if (reading.bill?.status === 'partial') {
            return { label: 'Sebagian', color: 'var(--md-sys-color-warning)', textColor: '#fff' };
        }
        return { label: 'Terbit', color: 'var(--md-sys-color-primary-container)', textColor: 'var(--md-sys-color-on-primary-container)' };
    };

    return (
        <AppLayout>
            <Head title={`Catat Meter ${MONTHS[period.month]} ${period.year}`} />
            <PageContainer>
                <TopAppBar
                    title={`${MONTHS[period.month]} ${period.year}`}
                    backHref="/catat-meter"
                    actions={
                        <a href={`/catat-meter/${period.id}/export/excel`} download>
                            <IconButton icon="download" />
                        </a>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.status && <Alert type="success" className="mb-4">{flash.status}</Alert>}
                    {flash?.error && <Alert type="error" className="mb-4">{flash.error}</Alert>}

                    {/* Summary Card - Enhanced */}
                    <Card variant="filled" className="mb-4" style={{ backgroundColor: 'var(--md-sys-color-primary-container)', color: 'var(--md-sys-color-on-primary-container)' }}>
                        <div className="md-label-medium" style={{ textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '16px', opacity: 0.8 }}>
                            Ringkasan Periode
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px' }}>
                            <div style={{ textAlign: 'center', padding: '12px', background: 'rgba(255,255,255,0.2)', borderRadius: '12px' }}>
                                <div className="md-display-small" style={{ fontWeight: 700, color: 'var(--md-sys-color-success)' }}>
                                    {summary.completed || 0}
                                </div>
                                <div className="md-label-small" style={{ marginTop: '4px' }}>
                                    Sudah Dicatat
                                </div>
                            </div>
                            <div style={{ textAlign: 'center', padding: '12px', background: 'rgba(255,255,255,0.2)', borderRadius: '12px' }}>
                                <div className="md-display-small" style={{ fontWeight: 700, color: 'var(--md-sys-color-error)' }}>
                                    {summary.pending || 0}
                                </div>
                                <div className="md-label-small" style={{ marginTop: '4px' }}>
                                    Belum Dicatat
                                </div>
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '8px', marginTop: '16px' }}>
                            <div style={{ textAlign: 'center' }}>
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{publishedCount}</div>
                                <div className="md-label-small">Terbit</div>
                            </div>
                            <div style={{ textAlign: 'center' }}>
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{paidCount}</div>
                                <div className="md-label-small">Lunas</div>
                            </div>
                            <div style={{ textAlign: 'center' }}>
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{summary.target || 0}</div>
                                <div className="md-label-small">Total</div>
                            </div>
                        </div>
                        <div style={{ marginTop: '16px', display: 'flex', gap: '16px', justifyContent: 'center', borderTop: '1px solid rgba(255,255,255,0.2)', paddingTop: '16px' }}>
                            <div className="md-body-small">
                                <strong>{summary.volume?.toFixed(1) || 0}</strong> m³
                            </div>
                            <div className="md-body-small">
                                <strong>Rp {(summary.bill || 0).toLocaleString('id-ID')}</strong>
                            </div>
                        </div>
                    </Card>

                    {/* Quick Action Buttons */}
                    {pendingReadings.length > 0 && (
                        <div className="mb-4">
                            <Link href={`/catat-meter/${period.id}/pending`}>
                                <Button
                                    variant="filled"
                                    fullWidth
                                    icon="edit_note"
                                    style={{ background: 'var(--md-sys-color-error)' }}
                                >
                                    {summary.pending || 0} Pelanggan Belum Dicatat
                                </Button>
                            </Link>
                        </div>
                    )}

                    {/* Search Bar */}
                    <div className="md-search-bar mb-4">
                        <span className="material-symbols-rounded">search</span>
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari pelanggan..."
                        />
                        {searchQuery && (
                            <button type="button" onClick={() => setSearchQuery('')} style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
                                <span className="material-symbols-rounded">close</span>
                            </button>
                        )}
                    </div>

                    {/* Area Filter Chips - For Admin only */}
                    {auth?.user?.is_admin && areas.length > 1 && (
                        <div className="mb-4">
                            <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Filter Operator/Area</div>
                            <div className="md-chip-group" style={{ overflowX: 'auto', whiteSpace: 'nowrap', paddingBottom: '8px' }}>
                                <button
                                    className={`md-chip ${!activeAreaId ? 'selected' : ''}`}
                                    onClick={() => handleAreaChange('')}
                                >
                                    <span className="material-symbols-rounded" style={{ fontSize: '18px' }}>apps</span>
                                    Semua
                                </button>
                                {areas.map(area => (
                                    <button
                                        key={area.id}
                                        className={`md-chip ${activeAreaId == area.id ? 'selected' : ''}`}
                                        onClick={() => handleAreaChange(area.id)}
                                    >
                                        {area.name}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Tab Filters */}
                    <div className="md-tab-bar mb-4">
                        <button className={`md-tab ${showTab === 'pending' ? 'active' : ''}`} onClick={() => setShowTab('pending')}>
                            Belum ({pendingReadings.length})
                        </button>
                        <button className={`md-tab ${showTab === 'completed' ? 'active' : ''}`} onClick={() => setShowTab('completed')}>
                            Selesai ({completedReadings.length})
                        </button>
                        <button className={`md-tab ${showTab === 'all' ? 'active' : ''}`} onClick={() => setShowTab('all')}>
                            Semua
                        </button>
                    </div>

                    {/* Readings List */}
                    <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                        {filteredReadings.length === 0 ? (
                            <EmptyState
                                icon={showTab === 'pending' ? 'check_circle' : 'water_drop'}
                                title={showTab === 'pending' ? 'Semua Selesai!' : 'Tidak Ada Data'}
                                message={showTab === 'pending' ? 'Tidak ada pelanggan yang belum dicatat.' : 'Tidak ada data yang cocok.'}
                            />
                        ) : (
                            filteredReadings.map(reading => {
                                const status = getStatusBadge(reading);
                                const canPublish = reading.recorded_at && !reading.bill_published_at;
                                const canUnpublish = auth?.user?.is_admin && reading.bill_published_at && (!reading.bill || reading.bill.paid_amount === 0);
                                const canPay = reading.bill_published_at && reading.bill && reading.bill.status !== 'paid';

                                return (
                                    <div key={reading.id} className="md-list-item" style={{ alignItems: 'flex-start', gap: '12px' }}>
                                        <div
                                            className="md-avatar sm"
                                            style={{
                                                backgroundColor: reading.recorded_at ? 'var(--md-sys-color-success)' : 'var(--md-sys-color-surface-variant)',
                                                color: reading.recorded_at ? 'var(--md-sys-color-on-success)' : 'var(--md-sys-color-on-surface-variant)',
                                                flexShrink: 0
                                            }}
                                        >
                                            {reading.recorded_at ? (
                                                <span className="material-symbols-rounded" style={{ fontSize: '18px' }}>check</span>
                                            ) : (
                                                <span className="material-symbols-rounded" style={{ fontSize: '18px' }}>schedule</span>
                                            )}
                                        </div>
                                        <div className="md-list-item__content" style={{ flex: 1, minWidth: 0 }}>
                                            <div className="md-body-large" style={{ fontWeight: 500 }}>{reading.customer?.name}</div>
                                            <div className="md-body-small text-muted">{reading.customer?.customer_code} • {reading.customer?.address_short}</div>
                                            {reading.recorded_at && (
                                                <div className="md-body-small" style={{ marginTop: '4px' }}>
                                                    {reading.start_reading} → {reading.end_reading} = <strong>{reading.usage_m3} m³</strong>
                                                    <span style={{ marginLeft: '8px' }}>Rp {(reading.bill_amount || 0).toLocaleString('id-ID')}</span>
                                                </div>
                                            )}
                                            <div style={{ display: 'flex', gap: '8px', marginTop: '8px', flexWrap: 'wrap', alignItems: 'center' }}>
                                                <span
                                                    className="md-badge sm"
                                                    style={{ backgroundColor: status.color, color: status.textColor }}
                                                >
                                                    {status.label}
                                                </span>
                                                {!reading.recorded_at && (
                                                    <Link href={`/catat-meter/${period.id}/input/${reading.id}`}>
                                                        <Button variant="filled" size="sm" icon="edit_note">Catat</Button>
                                                    </Link>
                                                )}
                                                {canPublish && (
                                                    <Button variant="tonal" size="sm" icon="publish" onClick={() => handlePublish(reading)}>
                                                        Terbitkan
                                                    </Button>
                                                )}
                                                {canUnpublish && (
                                                    <Button variant="text" size="sm" icon="undo" onClick={() => handleUnpublish(reading)}>
                                                        Batal
                                                    </Button>
                                                )}
                                                {canPay && (
                                                    <Button variant="filled" size="sm" icon="payments" onClick={() => openBillsSheet(reading)}>
                                                        Bayar
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })
                        )}
                    </Card>
                </div>

                {/* FAB to go to pending list for quick input */}
                {pendingReadings.length > 0 && showTab !== 'pending' && (
                    <FAB
                        icon="edit_note"
                        label={`${pendingReadings.length} Belum`}
                        variant="primary"
                        onClick={() => setShowTab('pending')}
                        style={{ position: 'fixed', bottom: '96px', right: '16px' }}
                    />
                )}

                {/* Publish Confirmation */}
                <ConfirmModal
                    isOpen={showPublishConfirm}
                    onClose={() => setShowPublishConfirm(false)}
                    onConfirm={confirmPublish}
                    title="Terbitkan Tagihan"
                    message={publishTarget ? `Terbitkan tagihan untuk ${publishTarget.customer?.name}? Tagihan: Rp ${(publishTarget.bill_amount || 0).toLocaleString('id-ID')}` : ''}
                    confirmText="Terbitkan"
                    variant="filled"
                />

                {/* Bills Selection Bottom Sheet */}
                <BottomSheet
                    isOpen={showBillsSheet}
                    onClose={() => setShowBillsSheet(false)}
                    title="Pilih Tagihan"
                >
                    {selectedReading && (
                        <div>
                            <Card variant="filled" className="mb-4">
                                <div className="md-body-large" style={{ fontWeight: 500 }}>{selectedReading.customer?.name}</div>
                                <div className="md-body-small text-muted">{selectedReading.customer?.customer_code}</div>
                            </Card>

                            {loadingBills ? (
                                <div style={{ textAlign: 'center', padding: '24px' }}>
                                    <div className="md-body-medium text-muted">Memuat tagihan...</div>
                                </div>
                            ) : customerBills.length === 0 ? (
                                <div style={{ textAlign: 'center', padding: '24px' }}>
                                    <div className="md-body-medium text-muted">Tidak ada tagihan aktif</div>
                                </div>
                            ) : (
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                    {customerBills.map(bill => (
                                        <Card
                                            key={bill.id}
                                            variant="outlined"
                                            className="cursor-pointer"
                                            onClick={() => selectBillForPayment(bill)}
                                            style={{ cursor: 'pointer' }}
                                        >
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <div>
                                                    <div className="md-body-large" style={{ fontWeight: 500 }}>
                                                        Periode {bill.period_label}
                                                    </div>
                                                    <div className="md-body-small text-muted">
                                                        {bill.usage_m3} m³ • {bill.status === 'partial' ? 'Sebagian dibayar' : 'Belum dibayar'}
                                                    </div>
                                                </div>
                                                <div style={{ textAlign: 'right' }}>
                                                    <div className="md-title-medium" style={{ color: 'var(--md-sys-color-error)', fontWeight: 600 }}>
                                                        Rp {(bill.remaining || 0).toLocaleString('id-ID')}
                                                    </div>
                                                    {bill.paid_amount > 0 && (
                                                        <div className="md-body-small text-muted">
                                                            Dibayar: Rp {bill.paid_amount.toLocaleString('id-ID')}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </BottomSheet>

                {/* Payment Bottom Sheet */}
                <BottomSheet
                    isOpen={showPaymentSheet}
                    onClose={() => setShowPaymentSheet(false)}
                    title="Pembayaran"
                >
                    {selectedReading && selectedBill && (
                        <form onSubmit={submitPayment} className="md-form-stack">
                            <Card variant="filled" className="mb-4">
                                <div className="md-body-large" style={{ fontWeight: 500 }}>{selectedReading.customer?.name}</div>
                                <div className="md-body-small text-muted">{selectedReading.customer?.customer_code} • Periode {selectedBill.period_label}</div>
                                <div className="md-title-large" style={{ marginTop: '8px', color: 'var(--md-sys-color-primary)' }}>
                                    Rp {(selectedBill.remaining || 0).toLocaleString('id-ID')}
                                </div>
                                {selectedBill.paid_amount > 0 && (
                                    <div className="md-body-small text-muted">
                                        Sudah dibayar: Rp {selectedBill.paid_amount.toLocaleString('id-ID')}
                                    </div>
                                )}
                            </Card>

                            <Input
                                label="Nominal Pembayaran"
                                icon="payments"
                                type="number"
                                value={paymentForm.data.amount}
                                onChange={(e) => paymentForm.setData('amount', e.target.value)}
                                error={paymentForm.errors.amount}
                                required
                            />

                            <Select
                                label="Metode Pembayaran"
                                icon="account_balance_wallet"
                                value={paymentForm.data.method}
                                onChange={(e) => paymentForm.setData('method', e.target.value)}
                                error={paymentForm.errors.method}
                            >
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </Select>

                            <Input
                                label="No. Referensi (Opsional)"
                                icon="tag"
                                type="text"
                                value={paymentForm.data.reference_number}
                                onChange={(e) => paymentForm.setData('reference_number', e.target.value)}
                            />

                            <Button type="submit" variant="filled" fullWidth loading={paymentForm.processing} icon="check">
                                Konfirmasi Pembayaran
                            </Button>
                        </form>
                    )}
                </BottomSheet>
            </PageContainer>
        </AppLayout>
    );
}
