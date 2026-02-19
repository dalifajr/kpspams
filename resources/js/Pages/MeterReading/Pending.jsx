import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';

const MONTHS = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export default function MeterPeriodPending({ period, pendingReadings = [], summary = {} }) {
    const { auth } = usePage().props;

    return (
        <AppLayout>
            <Head title={`Belum Dicatat - ${MONTHS[period.month]} ${period.year}`} />
            <PageContainer>
                <TopAppBar
                    title={`Belum Dicatat (${pendingReadings.length})`}
                    subtitle={`${MONTHS[period.month]} ${period.year}`}
                    backHref={`/catat-meter/${period.id}`}
                />

                <div style={{ padding: '0 16px' }}>
                    {/* Summary */}
                    <Card variant="filled" className="mb-4" style={{ backgroundColor: 'var(--md-sys-color-error-container)', color: 'var(--md-sys-color-on-error-container)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                            <div>
                                <div className="md-display-small" style={{ fontWeight: 700 }}>
                                    {pendingReadings.length}
                                </div>
                                <div className="md-label-medium">
                                    Pelanggan belum dicatat
                                </div>
                            </div>
                            <div style={{ textAlign: 'right' }}>
                                <div className="md-title-medium" style={{ fontWeight: 600 }}>
                                    {summary?.completed || 0} / {summary?.target || 0}
                                </div>
                                <div className="md-label-small">Selesai</div>
                            </div>
                        </div>
                    </Card>

                    {/* Pending Reading List */}
                    {pendingReadings.length > 0 ? (
                        <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                            {pendingReadings.map((reading, index) => (
                                <div key={reading.id} className="md-list-item" style={{ alignItems: 'center' }}>
                                    <div
                                        className="md-avatar sm"
                                        style={{
                                            backgroundColor: 'var(--md-sys-color-error-container)',
                                            color: 'var(--md-sys-color-on-error-container)',
                                            flexShrink: 0
                                        }}
                                    >
                                        <span className="material-symbols-rounded" style={{ fontSize: '18px' }}>schedule</span>
                                    </div>
                                    <div className="md-list-item__content" style={{ flex: 1 }}>
                                        <div className="md-body-large" style={{ fontWeight: 500 }}>{reading.customer?.name}</div>
                                        <div className="md-body-small text-muted">
                                            {reading.customer?.customer_code} • {reading.customer?.area?.name || reading.customer?.address_short}
                                        </div>
                                    </div>
                                    <Link href={`/catat-meter/${period.id}/input/${reading.id}`}>
                                        <Button variant="filled" size="sm" icon="edit_note">
                                            Catat
                                        </Button>
                                    </Link>
                                </div>
                            ))}
                        </Card>
                    ) : (
                        <EmptyState
                            icon="check_circle"
                            title="Semua Selesai!"
                            message="Tidak ada pelanggan yang belum dicatat meter."
                        />
                    )}

                    {/* Quick Navigation */}
                    <div style={{ marginTop: '24px', textAlign: 'center' }}>
                        <Link href={`/catat-meter/${period.id}`}>
                            <Button variant="text" icon="format_list_numbered">
                                Lihat Semua Pelanggan
                            </Button>
                        </Link>
                    </div>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
