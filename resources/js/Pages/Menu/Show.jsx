import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import Button from '@/Components/Button';

export default function MenuShow({ menu }) {
    return (
        <AppLayout>
            <Head title={menu.label} />
            <PageContainer>
                <TopAppBar title={menu.label} backHref="/dashboard" />

                <div style={{ 
                    display: 'flex', 
                    flexDirection: 'column', 
                    alignItems: 'center', 
                    justifyContent: 'center',
                    padding: '48px 24px',
                    textAlign: 'center' 
                }}>
                    <div style={{
                        width: '96px',
                        height: '96px',
                        borderRadius: 'var(--shape-expressive-large)',
                        background: menu.color ? `${menu.color}15` : 'var(--md-sys-color-surface-container-high)',
                        color: menu.color || 'var(--md-sys-color-primary)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        marginBottom: '24px'
                    }}>
                        <span className="material-symbols-rounded" style={{ fontSize: '48px' }}>{menu.icon}</span>
                    </div>

                    <h2 className="md-headline-small" style={{ marginBottom: '8px' }}>
                        Dalam Pengembangan
                    </h2>
                    <p className="md-body-medium text-muted" style={{ marginBottom: '24px', maxWidth: '300px' }}>
                        Modul <strong>{menu.label}</strong> sedang dalam tahap pengembangan. 
                        Fitur ini akan segera tersedia.
                    </p>

                    <div 
                        className="md-card-outlined" 
                        style={{ 
                            borderStyle: 'dashed', 
                            padding: '16px 24px',
                            borderRadius: 'var(--shape-expressive-medium)',
                            marginBottom: '24px'
                        }}
                    >
                        <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-outline)', marginRight: '8px' }}>
                            construction
                        </span>
                        Pengembangan Tahap Selanjutnya
                    </div>

                    <Button variant="tonal" href="/dashboard" icon="home">
                        Kembali ke Beranda
                    </Button>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
