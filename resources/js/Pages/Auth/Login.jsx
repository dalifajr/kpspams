import { Head, Link, useForm, usePage } from '@inertiajs/react';
import Input from '@/Components/Input';
import Button from '@/Components/Button';

export default function Login() {
    const { branding } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        phone_number: '',
        password: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Masuk" />
            <div className="md-auth-screen">
                <div className="md-auth-card">
                    {/* Logo & Header */}
                    <div className="md-auth-header">
                        <div className="md-auth-logo">
                            <span className="material-symbols-rounded">water_drop</span>
                        </div>
                        <div className="md-auth-meta">
                            <h1 className="md-headline-small">MeterPAMS</h1>
                            <p className="md-body-small text-muted">ver. {branding?.app_version || '1.0.0'}</p>
                        </div>
                    </div>

                    <div className="md-auth-content">
                        <h2 className="md-title-large" style={{ marginBottom: '8px' }}>Selamat Datang</h2>
                        <p className="md-body-medium text-muted" style={{ marginBottom: '24px' }}>
                            Aplikasi Pengelola Air Bersih Swadaya Masyarakat
                        </p>

                        <form onSubmit={handleSubmit} className="md-form-stack">
                            <Input
                                label="Nomor HP"
                                icon="phone_in_talk"
                                type="text"
                                value={data.phone_number}
                                onChange={(e) => setData('phone_number', e.target.value)}
                                placeholder="08xxxxxxxxxx"
                                error={errors.phone_number}
                                autoFocus
                                required
                            />

                            <Input
                                label="Password"
                                icon="lock"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Masukkan password"
                                error={errors.password}
                                required
                            />

                            <Button 
                                type="submit" 
                                variant="filled" 
                                fullWidth 
                                loading={processing}
                                icon="arrow_forward"
                                iconPosition="right"
                            >
                                Masuk
                            </Button>
                        </form>

                        <div className="md-auth-links">
                            <a href="#" className="md-link">Lupa Password?</a>
                            <Link href="/register" className="md-link">Daftar Akun</Link>
                        </div>
                    </div>

                    {/* Support Info */}
                    <div className="md-auth-footer">
                        <span className="md-label-small text-muted">Butuh bantuan?</span>
                        <a 
                            href={branding?.support_whatsapp_link} 
                            target="_blank" 
                            rel="noopener"
                            className="md-link"
                        >
                            <span className="material-symbols-rounded" style={{ fontSize: '16px' }}>support_agent</span>
                            {branding?.support_whatsapp || 'Hubungi Support'}
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
