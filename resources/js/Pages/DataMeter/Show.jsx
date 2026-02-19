import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Alert from '@/Components/Alert';
import Button, { IconButton } from '@/Components/Button';
import { BottomSheet, ConfirmModal } from '@/Components/Modal';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export default function DataMeterShow({
    period,
    areas = [],
    readings = [],
    summary = {},
    filters = {}
}) {
    const { flash, auth } = usePage().props;
    const [showUnpublishConfirm, setShowUnpublishConfirm] = useState(false);
    const [unpublishTarget, setUnpublishTarget] = useState(null);
    const [showDetailSheet, setShowDetailSheet] = useState(false);
    const [selectedReading, setSelectedReading] = useState(null);

    if (!auth?.user?.is_admin) {
        return (
            <AppLayout>
                <Head title="Data Meter" />
                <PageContainer>
                    <TopAppBar title="Data Meter" backHref="/menu/data-meter" />
                    <div style={{ padding: '0 16px' }}>
                        <Alert type="error">Anda tidak memiliki akses ke halaman ini.</Alert>
                    </div>
                </PageContainer>
            </AppLayout>
        );
    }

    const handleFilterChange = (key, value) => {
        router.get(`/menu/data-meter/${period.id}`, {
            ...filters,
            [key]: value,
        }, { preserveScroll: true, preserveState: true });
    };

    const handleUnpublish = (reading) => {
        setUnpublishTarget(reading);
        setShowUnpublishConfirm(true);
    };

    const confirmUnpublish = () => {
        if (!unpublishTarget) return;
        router.post(`/billing/${unpublishTarget.id}/unpublish`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                setShowUnpublishConfirm(false);
                setUnpublishTarget(null);
            }
        });
    };

    const openDetailSheet = (reading) => {
        setSelectedReading(reading);
        setShowDetailSheet(true);
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

    const statusOptions = [
        { value: 'all', label: 'Semua Status' },
        { value: 'recorded', label: 'Tercatat (Belum Terbit)' },
        { value: 'published', label: 'Terbit (Belum Lunas)' },
        { value: 'paid', label: 'Lunas' },
        { value: 'unpaid', label: 'Belum Lunas' },
    ];

    return (
        <AppLayout>
            <Head title={`Data Meter ${MONTHS[period.month]} ${period.year}`} />
            <PageContainer>
                <TopAppBar
                    title={`${MONTHS[period.month]} ${period.year}`}
                    subtitle="Data Meter"
                    backHref="/menu/data-meter"
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.status && <Alert type="success" className="mb-4">{flash.status}</Alert>}
                    {flash?.error && <Alert type="error" className="mb-4">{flash.error}</Alert>}

                    {/* Summary Card */}
                    <Card variant="filled" className="mb-4" style={{ backgroundColor: 'var(--md-sys-color-primary-container)', color: 'var(--md-sys-color-on-primary-container)' }}>
                        <div className="md-label-medium" style={{ textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '16px', opacity: 0.8 }}>
                            Ringkasan Data
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '12px', textAlign: 'center' }}>
                            <div>
                                <div className="md-title-large" style={{ fontWeight: 700 }}>{summary?.total || 0}</div>
                                <div className="md-label-small">Total</div>
                            </div>
                            <div>
                                <div className="md-title-large" style={{ fontWeight: 700, color: 'var(--md-sys-color-tertiary)' }}>{summary?.recorded || 0}</div>
                                <div className="md-label-small">Tercatat</div>
                            </div>
                            <div>
                                <div className="md-title-large" style={{ fontWeight: 700, color: 'var(--md-sys-color-primary)' }}>{summary?.published || 0}</div>
                                <div className="md-label-small">Terbit</div>
                            </div>
                            <div>
                                <div className="md-title-large" style={{ fontWeight: 700, color: 'var(--md-sys-color-success)' }}>{summary?.paid || 0}</div>
                                <div className="md-label-small">Lunas</div>
                            </div>
                        </div>
                        <div style={{ marginTop: '16px', textAlign: 'center', borderTop: '1px solid rgba(255,255,255,0.2)', paddingTop: '12px' }}>
                            <div className="md-body-small">
                                Total Tagihan: <strong>Rp {(summary?.total_bill || 0).toLocaleString('id-ID')}</strong>
                            </div>
                        </div>
                    </Card>

                    {/* Filters */}
                    <Card variant="outlined" className="mb-4">
                        <div className="md-label-medium text-muted" style={{ marginBottom: '12px' }}>Filter Data</div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                            <Select
                                label="Status"
                                value={filters.status || 'all'}
                                onChange={(e) => handleFilterChange('status', e.target.value)}
                            >
                                {statusOptions.map(opt => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </Select>
                            <Select
                                label="Area/Operator"
                                value={filters.area || ''}
                                onChange={(e) => handleFilterChange('area', e.target.value)}
                            >
                                <option value="">Semua Area</option>
                                {areas.map(area => (
                                    <option key={area.id} value={area.id}>{area.name}</option>
                                ))}
                            </Select>
                        </div>
                        <div style={{ marginTop: '12px' }}>
                            <Input
                                label="Cari Pelanggan"
                                icon="search"
                                type="text"
                                value={filters.search || ''}
                                onChange={(e) => handleFilterChange('search', e.target.value)}
                                placeholder="Nama atau kode pelanggan..."
                            />
                        </div>
                    </Card>

                    {/* Readings List */}
                    <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                        {readings.length === 0 ? (
                            <EmptyState
                                icon="water_drop"
                                title="Tidak Ada Data"
                                message="Tidak ada data meter yang cocok dengan filter."
                            />
                        ) : (
                            readings.map(reading => {
                                const status = getStatusBadge(reading);
                                const canUnpublish = reading.bill_published_at && (!reading.bill || reading.bill.paid_amount === 0);

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
                                            <div className="md-body-small text-muted">
                                                {reading.customer?.customer_code} • {reading.customer?.area?.name || reading.customer?.address_short}
                                            </div>
                                            {reading.recorded_at && (
                                                <div className="md-body-small" style={{ marginTop: '4px' }}>
                                                    {reading.start_reading} → {reading.end_reading} = <strong>{reading.usage_m3} m³</strong>
                                                    <span style={{ marginLeft: '8px' }}>Rp {(reading.bill_amount || 0).toLocaleString('id-ID')}</span>
                                                </div>
                                            )}
                                            {reading.bill_published_at && reading.bill && (
                                                <div className="md-body-small text-muted" style={{ marginTop: '2px' }}>
                                                    Dibayar: Rp {(reading.bill.paid_amount || 0).toLocaleString('id-ID')} / Rp {(reading.bill.total_amount || 0).toLocaleString('id-ID')}
                                                </div>
                                            )}
                                            <div style={{ display: 'flex', gap: '8px', marginTop: '8px', flexWrap: 'wrap', alignItems: 'center' }}>
                                                <span
                                                    className="md-badge sm"
                                                    style={{ backgroundColor: status.color, color: status.textColor }}
                                                >
                                                    {status.label}
                                                </span>
                                                <Button variant="text" size="sm" icon="info" onClick={() => openDetailSheet(reading)}>
                                                    Detail
                                                </Button>
                                                {canUnpublish && (
                                                    <Button variant="tonal" size="sm" icon="undo" onClick={() => handleUnpublish(reading)}>
                                                        Batal Terbit
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })
                        )}
                    </Card>

                    {/* Results count */}
                    <div className="md-body-small text-muted" style={{ textAlign: 'center', marginTop: '16px' }}>
                        Menampilkan {readings.length} dari {summary?.total || 0} data
                    </div>
                </div>

                {/* Unpublish Confirmation */}
                <ConfirmModal
                    isOpen={showUnpublishConfirm}
                    onClose={() => setShowUnpublishConfirm(false)}
                    onConfirm={confirmUnpublish}
                    title="Batalkan Penerbitan Tagihan"
                    message={unpublishTarget
                        ? `Batalkan penerbitan tagihan untuk ${unpublishTarget.customer?.name}? Tagihan akan dikembalikan ke status "Tercatat".`
                        : ''
                    }
                    confirmText="Ya, Batalkan"
                    variant="danger"
                />

                {/* Detail Bottom Sheet */}
                <BottomSheet
                    isOpen={showDetailSheet}
                    onClose={() => setShowDetailSheet(false)}
                    title="Detail Pencatatan"
                >
                    {selectedReading && (
                        <div>
                            <Card variant="filled" className="mb-4">
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{selectedReading.customer?.name}</div>
                                <div className="md-body-small text-muted">{selectedReading.customer?.customer_code}</div>
                                <div className="md-body-small text-muted">{selectedReading.customer?.address_short}</div>
                            </Card>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '16px' }}>
                                <Card variant="outlined">
                                    <div className="md-label-small text-muted">Area</div>
                                    <div className="md-body-medium">{selectedReading.customer?.area?.name || '-'}</div>
                                </Card>
                                <Card variant="outlined">
                                    <div className="md-label-small text-muted">Golongan</div>
                                    <div className="md-body-medium">{selectedReading.customer?.golongan?.name || '-'}</div>
                                </Card>
                            </div>

                            {selectedReading.recorded_at && (
                                <>
                                    <Card variant="outlined" className="mb-4">
                                        <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Data Meter</div>
                                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px', textAlign: 'center' }}>
                                            <div>
                                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{selectedReading.start_reading}</div>
                                                <div className="md-label-small text-muted">Stand Awal</div>
                                            </div>
                                            <div>
                                                <div className="md-title-medium" style={{ fontWeight: 600 }}>{selectedReading.end_reading}</div>
                                                <div className="md-label-small text-muted">Stand Akhir</div>
                                            </div>
                                            <div>
                                                <div className="md-title-medium" style={{ fontWeight: 600, color: 'var(--md-sys-color-primary)' }}>{selectedReading.usage_m3} m³</div>
                                                <div className="md-label-small text-muted">Pemakaian</div>
                                            </div>
                                        </div>
                                    </Card>

                                    <Card variant="outlined" className="mb-4">
                                        <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Informasi Pencatatan</div>
                                        <div className="md-body-small">
                                            <strong>Petugas:</strong> {selectedReading.petugas?.name || '-'}
                                        </div>
                                        <div className="md-body-small">
                                            <strong>Dicatat:</strong> {selectedReading.recorded_at ? new Date(selectedReading.recorded_at).toLocaleString('id-ID') : '-'}
                                        </div>
                                        {selectedReading.note && (
                                            <div className="md-body-small" style={{ marginTop: '8px' }}>
                                                <strong>Catatan:</strong> {selectedReading.note}
                                            </div>
                                        )}
                                    </Card>
                                </>
                            )}

                            {selectedReading.bill_published_at && (
                                <Card variant="outlined" className="mb-4">
                                    <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Informasi Tagihan</div>
                                    <div className="md-body-small">
                                        <strong>Tagihan:</strong> Rp {(selectedReading.bill?.total_amount || selectedReading.bill_amount || 0).toLocaleString('id-ID')}
                                    </div>
                                    <div className="md-body-small">
                                        <strong>Dibayar:</strong> Rp {(selectedReading.bill?.paid_amount || 0).toLocaleString('id-ID')}
                                    </div>
                                    <div className="md-body-small">
                                        <strong>Sisa:</strong> Rp {(selectedReading.bill?.remaining || 0).toLocaleString('id-ID')}
                                    </div>
                                    <div className="md-body-small">
                                        <strong>Status:</strong> {selectedReading.bill?.status === 'paid' ? 'Lunas' : selectedReading.bill?.status === 'partial' ? 'Sebagian' : 'Terbit'}
                                    </div>
                                    <div className="md-body-small">
                                        <strong>Diterbitkan oleh:</strong> {selectedReading.bill_published_by_user?.name || '-'}
                                    </div>
                                    <div className="md-body-small">
                                        <strong>Tanggal terbit:</strong> {selectedReading.bill_published_at ? new Date(selectedReading.bill_published_at).toLocaleString('id-ID') : '-'}
                                    </div>
                                </Card>
                            )}

                            {selectedReading.bill?.payments && selectedReading.bill.payments.length > 0 && (
                                <Card variant="outlined">
                                    <div className="md-label-small text-muted" style={{ marginBottom: '8px' }}>Riwayat Pembayaran</div>
                                    {selectedReading.bill.payments.map((payment, idx) => (
                                        <div key={payment.id || idx} className="md-body-small" style={{ padding: '8px 0', borderBottom: '1px solid var(--md-sys-color-outline-variant)' }}>
                                            <div><strong>Rp {(payment.amount || 0).toLocaleString('id-ID')}</strong> via {payment.method?.toUpperCase()}</div>
                                            <div className="text-muted">{payment.paid_at ? new Date(payment.paid_at).toLocaleString('id-ID') : '-'}</div>
                                        </div>
                                    ))}
                                </Card>
                            )}
                        </div>
                    )}
                </BottomSheet>
            </PageContainer>
        </AppLayout>
    );
}
