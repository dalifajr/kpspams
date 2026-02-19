import { Head, Link, usePage, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, Section } from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import Button from '@/Components/Button';

export default function Profile() {
    const { auth, branding } = usePage().props;
    const user = auth?.user;

    const handleLogout = () => {
        router.post('/logout');
    };

    if (!user) return null;

    const getRoleBadgeClass = () => {
        if (user.is_admin) return 'admin';
        if (user.is_petugas) return 'success';
        return 'primary';
    };

    const getRoleLabel = () => {
        if (user.is_admin) return 'Admin';
        if (user.is_petugas) return 'Petugas';
        return 'Pelanggan';
    };

    return (
        <AppLayout>
            <Head title="Profil" />
            <PageContainer>
                {/* Profile Hero */}
                <header className="md-hero-card" style={{ marginBottom: '16px' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                        <div className="md-avatar lg">
                            {user.avatar_path ? (
                                <img src={`/storage/${user.avatar_path}`} alt={user.name} />
                            ) : (
                                user.name.charAt(0).toUpperCase()
                            )}
                        </div>
                        <div>
                            <h1 className="md-headline-small" style={{ margin: 0, color: '#fff' }}>{user.name}</h1>
                            <p className="md-body-medium" style={{ margin: '4px 0 8px', opacity: 0.85 }}>{user.phone_number}</p>
                            <span className={`md-badge ${getRoleBadgeClass()}`}>
                                {getRoleLabel()}
                            </span>
                        </div>
                    </div>
                </header>

                {/* Contact Info */}
                <Card variant="filled" className="mb-4">
                    <div className="md-list-item">
                        <span className="material-symbols-rounded md-list-item__icon">mail</span>
                        <div className="md-list-item__content">
                            <div className="md-label-medium text-muted">Email</div>
                            <div className="md-body-medium">{user.email || '-'}</div>
                        </div>
                    </div>
                    <div className="md-list-item">
                        <span className="material-symbols-rounded md-list-item__icon">location_on</span>
                        <div className="md-list-item__content">
                            <div className="md-label-medium text-muted">Alamat</div>
                            <div className="md-body-medium">{user.address_short || '-'}</div>
                        </div>
                    </div>
                </Card>

                {/* Quick Menu */}
                <Section title="Menu Cepat">
                    <Card variant="outlined">
                        <Link href="/dashboard" className="md-list-item">
                            <div className="md-list-item__leading primary">
                                <span className="material-symbols-rounded">home</span>
                            </div>
                            <div className="md-list-item__content">
                                <div className="md-list-item__headline">Beranda</div>
                            </div>
                            <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
                        </Link>
                        {(user.is_admin || user.is_petugas) && (
                            <Link href="/catat-meter" className="md-list-item">
                                <div className="md-list-item__leading tertiary">
                                    <span className="material-symbols-rounded">edit_note</span>
                                </div>
                                <div className="md-list-item__content">
                                    <div className="md-list-item__headline">Catat Meter</div>
                                </div>
                                <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
                            </Link>
                        )}
                    </Card>
                </Section>

                {/* About App */}
                <Section title="Tentang Aplikasi">
                    <Card variant="filled">
                        <div style={{ marginBottom: '12px' }}>
                            <p className="md-title-medium" style={{ margin: 0 }}>{branding?.community_name}</p>
                            <p className="md-body-medium text-muted" style={{ margin: '4px 0 0' }}>{branding?.region_name}</p>
                            <p className="md-label-small text-muted" style={{ margin: '4px 0 0' }}>v{branding?.app_version}</p>
                        </div>
                        <a 
                            href={branding?.support_whatsapp_link} 
                            target="_blank" 
                            rel="noopener" 
                            className="md-list-item"
                            style={{ 
                                background: 'var(--md-sys-color-success-container)', 
                                borderRadius: 'var(--shape-expressive-medium)',
                                margin: '0 -16px -16px',
                                padding: '12px 16px',
                                textDecoration: 'none',
                                color: 'var(--md-sys-color-on-success-container)'
                            }}
                        >
                            <span className="material-symbols-rounded">support_agent</span>
                            <span style={{ marginLeft: '8px' }}>Hubungi Support</span>
                        </a>
                    </Card>
                </Section>

                {/* Logout */}
                <Button 
                    variant="danger" 
                    fullWidth 
                    icon="logout" 
                    onClick={handleLogout}
                    style={{ marginTop: '8px', marginBottom: '24px' }}
                >
                    Keluar
                </Button>
            </PageContainer>
        </AppLayout>
    );
}
