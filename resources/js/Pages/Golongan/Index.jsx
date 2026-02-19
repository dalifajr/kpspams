import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import { FAB } from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function GolonganIndex({ golongans = [] }) {
    const { flash } = usePage().props;

    return (
        <AppLayout>
            <Head title="Golongan Tarif" />
            <PageContainer>
                <TopAppBar 
                    title="Golongan Tarif" 
                    backHref="/dashboard"
                    actions={
                        <div className="md-badge primary">{golongans.length}</div>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.success && (
                        <Alert type="success" className="mb-4">{flash.success}</Alert>
                    )}

                    {golongans.length > 0 ? (
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                            {golongans.map((golongan) => (
                                <Link 
                                    key={golongan.id} 
                                    href={`/menu/golongan/${golongan.id}`} 
                                    className="md-card-outlined" 
                                    style={{ display: 'block', textDecoration: 'none', color: 'inherit' }}
                                >
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '12px' }}>
                                        <h3 className="md-title-medium" style={{ margin: 0 }}>{golongan.name}</h3>
                                        <span className="md-badge primary">{golongan.code}</span>
                                    </div>
                                    <div style={{ display: 'flex', gap: '24px' }}>
                                        <div>
                                            <div className="md-label-small text-muted">LEVEL TARIF</div>
                                            <div className="md-title-medium">{golongan.tariff_levels_count || 0}</div>
                                        </div>
                                        <div>
                                            <div className="md-label-small text-muted">PELANGGAN</div>
                                            <div className="md-title-medium">{golongan.customers_count || 0}</div>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon="category"
                            title="Belum Ada Golongan"
                            message="Tambahkan golongan tarif untuk mengatur biaya air"
                        />
                    )}
                </div>

                <FAB icon="add" label="Golongan Baru" href="/menu/golongan/create" />
            </PageContainer>
        </AppLayout>
    );
}
