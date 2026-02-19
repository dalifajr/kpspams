import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import { FAB } from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function AreasIndex({ areas = [] }) {
    const { flash } = usePage().props;

    return (
        <AppLayout>
            <Head title="Area Layanan" />
            <PageContainer>
                <TopAppBar 
                    title="Area Layanan" 
                    backHref="/dashboard"
                    actions={
                        <div className="md-badge primary">{areas.length}</div>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.success && (
                        <Alert type="success" className="mb-4">{flash.success}</Alert>
                    )}

                    {areas.length > 0 ? (
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                            {areas.map((area) => (
                                <Link 
                                    key={area.id} 
                                    href={`/menu/area/${area.id}`} 
                                    className="md-card" 
                                    style={{ display: 'flex', alignItems: 'center', gap: '16px', textDecoration: 'none', color: 'inherit' }}
                                >
                                    <div className="md-list-item__leading secondary">
                                        <span className="material-symbols-rounded">location_on</span>
                                    </div>
                                    <div style={{ flex: 1 }}>
                                        <div className="md-title-medium">{area.name}</div>
                                        <div className="md-body-small text-muted">
                                            {area.customer_count || 0} Pelanggan • {area.petugas?.length || 0} Petugas
                                        </div>
                                    </div>
                                    <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon="map"
                            title="Belum Ada Area"
                            message="Tambahkan area layanan untuk mulai mengelola pelanggan"
                        />
                    )}
                </div>

                <FAB icon="add" label="Area Baru" href="/menu/area/create" />
            </PageContainer>
        </AppLayout>
    );
}
