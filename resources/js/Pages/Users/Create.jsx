import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Button from '@/Components/Button';

export default function UsersCreate({ roleOptions = [], areas = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone_number: '',
        role: 'user',
        password: '',
        area_id: '',
        address_short: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/menu/user');
    };

    return (
        <AppLayout>
            <Head title="Tambah Pengguna" />
            <PageContainer>
                <TopAppBar title="Tambah Pengguna" backHref="/menu/user" />

                <div style={{ padding: '0 16px' }}>
                    <form onSubmit={handleSubmit}>
                        <Card variant="elevated" className="mb-4">
                            <div className="md-form-stack">
                                <Input
                                    label="Nama Lengkap"
                                    icon="person"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={errors.name}
                                    required
                                />

                                <Input
                                    label="Nomor HP"
                                    icon="phone"
                                    type="text"
                                    value={data.phone_number}
                                    onChange={(e) => setData('phone_number', e.target.value)}
                                    placeholder="08xxxxxxxxxx"
                                    error={errors.phone_number}
                                    required
                                />

                                <Input
                                    label="Email"
                                    icon="mail"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="nama@email.com"
                                    error={errors.email}
                                    required
                                />

                                <Select
                                    label="Peran"
                                    icon="badge"
                                    value={data.role}
                                    onChange={(e) => setData('role', e.target.value)}
                                    error={errors.role}
                                >
                                    <option value="user">User</option>
                                    <option value="petugas">Petugas</option>
                                    <option value="admin">Admin</option>
                                </Select>

                                {areas.length > 0 && (
                                    <Select
                                        label="Area"
                                        icon="grid_view"
                                        value={data.area_id}
                                        onChange={(e) => setData('area_id', e.target.value)}
                                        error={errors.area_id}
                                        required
                                    >
                                        <option value="">Pilih Area</option>
                                        {areas.map((area) => (
                                            <option key={area.id} value={area.id}>{area.name}</option>
                                        ))}
                                    </Select>
                                )}

                                <Input
                                    label="Alamat Singkat (Opsional)"
                                    icon="location_on"
                                    type="text"
                                    value={data.address_short}
                                    onChange={(e) => setData('address_short', e.target.value)}
                                    error={errors.address_short}
                                />

                                <Input
                                    label="Kata Sandi"
                                    icon="lock"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    error={errors.password}
                                    required
                                />
                            </div>
                        </Card>

                        <div className="flex gap-3 justify-end">
                            <Button variant="text" href="/menu/user">Batal</Button>
                            <Button type="submit" variant="filled" loading={processing} icon="check">
                                Simpan
                            </Button>
                        </div>
                    </form>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
