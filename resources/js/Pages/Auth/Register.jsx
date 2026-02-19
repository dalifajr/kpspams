import { Head, Link, useForm } from '@inertiajs/react';
import Input, { Select } from '@/Components/Input';
import Button from '@/Components/Button';

export default function Register({ areas = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        phone_number: '',
        address_short: '',
        area_id: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <>
            <Head title="Daftar Akun" />
            <div className="md-auth-screen">
                <div className="md-auth-card">
                    {/* Header */}
                    <div className="md-auth-header">
                        <div className="md-auth-logo secondary">
                            <span className="material-symbols-rounded">person_add</span>
                        </div>
                        <div className="md-auth-meta">
                            <h1 className="md-headline-small">Daftar Akun</h1>
                            <p className="md-body-small text-muted">Silakan lengkapi data berikut</p>
                        </div>
                    </div>

                    <div className="md-auth-content">
                        <form onSubmit={handleSubmit} className="md-form-stack">
                            <Input
                                label="Nama Lengkap"
                                icon="person"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Masukkan nama lengkap"
                                error={errors.name}
                                required
                            />

                            <Input
                                label="Nomor HP"
                                icon="phone_in_talk"
                                type="text"
                                value={data.phone_number}
                                onChange={(e) => setData('phone_number', e.target.value)}
                                placeholder="08xxxxxxxxxx"
                                error={errors.phone_number}
                                required
                            />

                            <Input
                                label="Alamat Singkat"
                                icon="location_on"
                                type="text"
                                value={data.address_short}
                                onChange={(e) => setData('address_short', e.target.value)}
                                placeholder="Contoh: RT 01 RW 02"
                                error={errors.address_short}
                            />

                            <Select
                                label="Area"
                                icon="grid_view"
                                value={data.area_id}
                                onChange={(e) => setData('area_id', e.target.value)}
                                error={errors.area_id}
                            >
                                <option value="">Pilih Area</option>
                                {areas.map((area) => (
                                    <option key={area.id} value={area.id}>{area.name}</option>
                                ))}
                            </Select>

                            <Input
                                label="Password"
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
                                placeholder="Ulangi password"
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
                                Daftar
                            </Button>
                        </form>

                        <div className="md-auth-links">
                            <Link href="/login" className="md-link">Sudah punya akun? Masuk</Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
