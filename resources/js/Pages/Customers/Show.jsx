import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Button, { IconButton } from '@/Components/Button';
import Alert from '@/Components/Alert';
import { ConfirmModal } from '@/Components/Modal';

export default function CustomerShow({ customer, areas = [], golongans = [] }) {
    const { flash } = usePage().props;
    const [isEditing, setIsEditing] = useState(false);
    const [showCreateAccount, setShowCreateAccount] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const { data, setData, put, processing } = useForm({
        name: customer.name,
        address_short: customer.address_short,
        area_id: customer.area_id,
        golongan_id: customer.golongan_id,
        phone_number: customer.phone_number || '',
    });

    const accountForm = useForm({ phone_number: customer.phone_number || '', password: '' });

    const handleUpdate = (e) => {
        e.preventDefault();
        put(`/menu/data-pelanggan/${customer.id}`, { onSuccess: () => setIsEditing(false) });
    };

    const handleDelete = () => {
        router.delete(`/menu/data-pelanggan/${customer.id}`);
    };

    const handleCreateAccount = (e) => {
        e.preventDefault();
        accountForm.post(`/menu/data-pelanggan/${customer.id}/account`, {
            onSuccess: () => setShowCreateAccount(false)
        });
    };

    const generatePassword = () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        let out = '';
        for (let i = 0; i < 12; i++) out += chars[Math.floor(Math.random() * chars.length)];
        accountForm.setData('password', out);
    };

    const buildWhatsAppLink = (phoneNumber, message) => {
        if (!phoneNumber) return null;
        let digits = String(phoneNumber).replace(/\D+/g, '');
        if (!digits) return null;
        if (digits.startsWith('0')) digits = `62${digits.slice(1)}`;
        if (!digits.startsWith('62')) digits = `62${digits}`;
        return `https://wa.me/${digits}?text=${encodeURIComponent(message)}`;
    };

    return (
        <AppLayout>
            <Head title={customer.name} />
            <PageContainer>
                <TopAppBar 
                    title="Detail Pelanggan" 
                    backHref="/menu/data-pelanggan"
                    actions={
                        <IconButton 
                            icon={isEditing ? 'close' : 'edit'} 
                            onClick={() => setIsEditing(!isEditing)} 
                        />
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.generated_credentials && (
                        <Alert type="success" className="mb-4" title="Akun Berhasil Dibuat!">
                            <div>Login: <strong>{flash.generated_credentials.phone_number}</strong></div>
                            <div>Password: <strong>{flash.generated_credentials.password}</strong></div>
                            <div style={{ display: 'flex', gap: '8px', marginTop: '12px', flexWrap: 'wrap' }}>
                                <Button
                                    variant="tonal"
                                    icon="content_copy"
                                    onClick={() => {
                                        const msg = `Akun MeterPAMS Anda sudah dibuat.\nLogin: ${flash.generated_credentials.phone_number}\nPassword: ${flash.generated_credentials.password}\nSilakan login di ${window.location.origin} dan ganti password saat login pertama.`;
                                        if (navigator?.clipboard?.writeText) {
                                            navigator.clipboard.writeText(msg);
                                        }
                                    }}
                                >
                                    Copy
                                </Button>
                                <Button
                                    variant="filled"
                                    icon="send"
                                    onClick={() => {
                                        const msg = `Akun MeterPAMS Anda sudah dibuat.\nLogin: ${flash.generated_credentials.phone_number}\nPassword: ${flash.generated_credentials.password}\nSilakan login di ${window.location.origin} dan ganti password saat login pertama.`;
                                        const link = buildWhatsAppLink(flash.generated_credentials.phone_number, msg);
                                        if (link) window.open(link, '_blank', 'noopener');
                                    }}
                                >
                                    Kirim WhatsApp
                                </Button>
                            </div>
                        </Alert>
                    )}

                    {isEditing ? (
                        <form onSubmit={handleUpdate}>
                            <Card variant="elevated" className="mb-4">
                                <div className="md-form-stack">
                                    <Input
                                        label="Nama Lengkap"
                                        icon="person"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    <Input
                                        label="Alamat"
                                        icon="location_on"
                                        type="text"
                                        value={data.address_short}
                                        onChange={(e) => setData('address_short', e.target.value)}
                                        required
                                    />
                                    <Select
                                        label="Area"
                                        icon="grid_view"
                                        value={data.area_id}
                                        onChange={(e) => setData('area_id', e.target.value)}
                                    >
                                        {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                    </Select>
                                    <Select
                                        label="Golongan"
                                        icon="category"
                                        value={data.golongan_id}
                                        onChange={(e) => setData('golongan_id', e.target.value)}
                                    >
                                        {golongans.map(g => <option key={g.id} value={g.id}>{g.name}</option>)}
                                    </Select>
                                </div>
                            </Card>
                            <Button type="submit" variant="filled" fullWidth loading={processing} icon="check">
                                Simpan Perubahan
                            </Button>
                        </form>
                    ) : (
                        <>
                            {/* Customer Info Card */}
                            <Card variant="filled" className="mb-4">
                                <div style={{ display: 'flex', gap: '16px', alignItems: 'center', marginBottom: '16px' }}>
                                    <div className="md-avatar lg">
                                        {customer.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <h2 className="md-title-large" style={{ margin: 0 }}>{customer.name}</h2>
                                        <div className="md-body-medium text-muted">{customer.customer_code}</div>
                                    </div>
                                </div>

                                <div className="md-list-item">
                                    <span className="material-symbols-rounded md-list-item__icon">location_on</span>
                                    <div className="md-list-item__content">
                                        <div className="md-label-medium text-muted">Alamat</div>
                                        <div className="md-body-medium">{customer.address_short} ({customer.area?.name})</div>
                                    </div>
                                </div>
                                <div className="md-list-item">
                                    <span className="material-symbols-rounded md-list-item__icon">category</span>
                                    <div className="md-list-item__content">
                                        <div className="md-label-medium text-muted">Golongan</div>
                                        <div className="md-body-medium">{customer.golongan?.name}</div>
                                    </div>
                                </div>
                                <div className="md-list-item">
                                    <span className="material-symbols-rounded md-list-item__icon">call</span>
                                    <div className="md-list-item__content">
                                        <div className="md-label-medium text-muted">Telepon</div>
                                        <div className="md-body-medium">{customer.phone_number || '-'}</div>
                                    </div>
                                </div>
                            </Card>

                            {/* Digital Account Section */}
                            <Section title="Akun Digital">
                                <Card variant="outlined">
                                    {customer.users && customer.users.length > 0 ? (
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', padding: '8px 0' }}>
                                            <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-success)' }}>verified</span>
                                            <span className="md-body-medium">Sudah terhubung dengan akun user</span>
                                        </div>
                                    ) : showCreateAccount ? (
                                        <form onSubmit={handleCreateAccount} style={{ display: 'flex', gap: '8px', alignItems: 'flex-end' }}>
                                            <Input
                                                label="Nomor HP Login"
                                                type="text"
                                                value={accountForm.data.phone_number}
                                                onChange={(e) => accountForm.setData('phone_number', e.target.value)}
                                                placeholder="08xxxxxxxxxx"
                                                error={accountForm.errors.phone_number}
                                                required
                                                style={{ flex: 1 }}
                                            />
                                            <div style={{ display: 'flex', gap: '8px', alignItems: 'flex-end', flex: 1 }}>
                                                <Input
                                                    label="Password"
                                                    type="password"
                                                    value={accountForm.data.password}
                                                    onChange={(e) => accountForm.setData('password', e.target.value)}
                                                    placeholder="(boleh kosong untuk acak)"
                                                    error={accountForm.errors.password}
                                                    style={{ flex: 1 }}
                                                />
                                                <Button type="button" variant="tonal" icon="casino" onClick={generatePassword}>
                                                    Acak
                                                </Button>
                                            </div>
                                            <Button type="submit" variant="filled" loading={accountForm.processing}>
                                                Buat
                                            </Button>
                                        </form>
                                    ) : (
                                        <Button 
                                            variant="tonal" 
                                            fullWidth 
                                            icon="person_add" 
                                            onClick={() => setShowCreateAccount(true)}
                                        >
                                            Buat Akun Login
                                        </Button>
                                    )}
                                </Card>
                            </Section>

                            <div style={{ textAlign: 'center', marginTop: '24px' }}>
                                <Button 
                                    variant="text" 
                                    onClick={() => setShowDeleteModal(true)}
                                    style={{ color: 'var(--md-sys-color-error)' }}
                                    icon="delete"
                                >
                                    Hapus Pelanggan
                                </Button>
                            </div>
                        </>
                    )}
                </div>
            </PageContainer>

            <ConfirmModal
                isOpen={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={handleDelete}
                title="Hapus Pelanggan"
                message={`Apakah Anda yakin ingin menghapus pelanggan "${customer.name}"?`}
                confirmText="Hapus"
                variant="danger"
            />
        </AppLayout>
    );
}
