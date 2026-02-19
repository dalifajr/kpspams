import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, Section } from '@/Layouts/AppLayout';
import MenuCard from '@/Components/MenuCard';

export default function Dashboard({ adminMenus = [], operatorMenus = [], consumerMenus = [], showAdminSection = false }) {
    const { auth, branding } = usePage().props;
    const user = auth?.user;

    return (
        <AppLayout>
            <Head title="Dashboard" />
            <PageContainer style={{ padding: 0 }}>
                {/* Hero Header */}
                <header className="md-hero-card md-hero-card--sticky">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start', marginBottom: '24px' }}>
                        <div>
                            <div className="md-label-medium" style={{ opacity: 0.9 }}>{branding?.community_name}</div>
                            <h1 className="md-headline-medium" style={{ margin: 0 }}>{branding?.region_name}</h1>
                        </div>
                        <div className="md-avatar">
                            {user?.name?.charAt(0).toUpperCase()}
                        </div>
                    </div>
                    <div>
                        <div className="md-title-large">Hai, {user?.name}</div>
                        <div className="md-body-medium" style={{ opacity: 0.8 }}>Selamat datang kembali</div>
                    </div>
                </header>

                <div style={{ padding: '24px 16px 100px' }}>
                    {showAdminSection && adminMenus.length > 0 && (
                        <Section title="Menu Admin">
                            <div className="md-grid">
                                {adminMenus.map((menu, index) => (
                                    <MenuCard key={menu.slug} menu={menu} number={String(index + 1).padStart(2, '0')} />
                                ))}
                            </div>
                        </Section>
                    )}

                    {operatorMenus.length > 0 && (
                        <Section title="Operasional">
                            <div className="md-grid">
                                {operatorMenus.map((menu, index) => (
                                    <MenuCard key={menu.slug} menu={menu} number={String(index + 1).padStart(2, '0')} />
                                ))}
                            </div>
                        </Section>
                    )}

                    {consumerMenus.length > 0 && (
                        <Section title="Layanan Mandiri">
                            <div className="md-grid">
                                {consumerMenus.map((menu, index) => (
                                    <MenuCard key={menu.slug} menu={menu} number={String(index + 1).padStart(2, '0')} />
                                ))}
                            </div>
                        </Section>
                    )}

                    {/* Support Links */}
                    <Section title="Bantuan" style={{ marginTop: '8px' }}>
                        <div className="md-support-bar" style={{ margin: 0 }}>
                            <a href={branding?.support_whatsapp_link} target="_blank" rel="noopener noreferrer" className="md-support-card">
                                <span className="material-symbols-rounded">support_agent</span>
                                <div className="md-label-medium">WhatsApp</div>
                            </a>
                            <a href={branding?.support_telegram} target="_blank" rel="noopener noreferrer" className="md-support-card">
                                <span className="material-symbols-rounded">forum</span>
                                <div className="md-label-medium">Telegram</div>
                            </a>
                        </div>
                    </Section>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
