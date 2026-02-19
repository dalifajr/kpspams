import { Head, useForm } from '@inertiajs/react';
import Input from '@/Components/Input';
import Button from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function ForcePassword() {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/password/force-update');
    };

    return (
        <>
            <Head title="Update Password" />
            <div className="md-auth-screen">
                <div className="md-auth-card">
                    {/* Header */}
                    <div className="md-auth-header">
                        <div className="md-auth-logo warning">
                            <span className="material-symbols-rounded">key</span>
                        </div>
                        <div className="md-auth-meta">
                            <h1 className="md-headline-small">Update Password</h1>
                            <p className="md-body-small text-muted">Perbarui password untuk keamanan</p>
                        </div>
                    </div>

                    <div className="md-auth-content">
                        <Alert type="warning" dismissible={false} style={{ marginBottom: '20px' }}>
                            Anda harus memperbarui password untuk melanjutkan menggunakan aplikasi.
                        </Alert>

                        <form onSubmit={handleSubmit} className="md-form-stack">
                            <Input
                                label="Password Baru"
                                icon="lock"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Minimal 8 karakter"
                                error={errors.password}
                                required
                            />

                            <Input
                                label="Konfirmasi Password"
                                icon="lock"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Ulangi password baru"
                                required
                            />

                            <Button 
                                type="submit" 
                                variant="filled" 
                                fullWidth 
                                loading={processing}
                                icon="check"
                                iconPosition="right"
                            >
                                Simpan Password
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
