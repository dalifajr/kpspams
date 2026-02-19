import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Button, { IconButton } from '@/Components/Button';
import Alert from '@/Components/Alert';
import Modal, { ConfirmModal } from '@/Components/Modal';

export default function UsersShow({ managedUser, areas = [], whatsappLink }) {
    const { flash } = usePage().props;
    const [isEditing, setIsEditing] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const { auth } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        edit_user_id: managedUser.id,
        name: managedUser.name,
        email: managedUser.email || '',
        phone_number: managedUser.phone_number || '',
        area_id: managedUser.area_id || '',
        address_short: managedUser.address_short || '',
    });
    const roleForm = useForm({ role: managedUser.role });
    const passwordForm = useForm({ password: '', password_confirmation: '' });
    const approveForm = useForm({ notify: false });

    const handleUpdate = (e) => {
        e.preventDefault();
        put(`/menu/user/${managedUser.id}`, { errorBag: 'editUser', onSuccess: () => setIsEditing(false) });
    };

    const handleUpdateRole = () => {
        roleForm.patch(`/menu/user/${managedUser.id}/role`, {
            preserveScroll: true,
        });
    };

    const handleUpdatePassword = (e) => {
        e.preventDefault();
        passwordForm.patch(`/menu/user/${managedUser.id}/password`, {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const handleDelete = () => {
        router.delete(`/menu/user/${managedUser.id}`);
    };

    const handleApprove = () => {
        approveForm.patch(`/menu/user/${managedUser.id}/approve`, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout>
            <Head title={managedUser.name} />
            <PageContainer>
                <TopAppBar 
                    title="Detail Pengguna" 
                    backHref="/menu/user"
                    actions={
                        <IconButton 
                            icon={isEditing ? 'close' : 'edit'} 
                            onClick={() => setIsEditing(!isEditing)} 
                        />
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.status && (
                        <Alert type="success" className="mb-4">{flash.status}</Alert>
                    )}
                    {flash?.error && (
                        <Alert type="error" className="mb-4">{flash.error}</Alert>
                    )}
                    {/* User Avatar & Info */}
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '16px', marginBottom: '24px' }}>
                        <div className="md-avatar xl">
                            {managedUser.name.charAt(0).toUpperCase()}
                        </div>
                        <div style={{ textAlign: 'center' }}>
                            <h2 className="md-headline-medium" style={{ margin: 0 }}>{managedUser.name}</h2>
                            <div className="md-body-medium text-muted" style={{ marginTop: '4px' }}>
                                {managedUser.email || managedUser.phone_number}
                            </div>
                            <span className={`md-badge ${managedUser.role === 'admin' ? 'admin' : managedUser.role === 'petugas' ? 'success' : 'primary'}`} style={{ marginTop: '8px' }}>
                                {managedUser.role}
                            </span>
                        </div>
                    </div>

                    {isEditing ? (
                        <>
                            <form onSubmit={handleUpdate}>
                                <Card variant="elevated" className="mb-4">
                                    <div className="md-form-stack">
                                        <Input
                                            label="Nama"
                                            icon="person"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            error={errors.name}
                                            required
                                        />
                                        <Input
                                            label="Email"
                                            icon="mail"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            error={errors.email}
                                            required
                                        />
                                        <Input
                                            label="Nomor HP"
                                            icon="phone"
                                            type="text"
                                            value={data.phone_number}
                                            onChange={(e) => setData('phone_number', e.target.value)}
                                            error={errors.phone_number}
                                            required
                                        />
                                        <Select
                                            label="Area"
                                            icon="grid_view"
                                            value={data.area_id}
                                            onChange={(e) => setData('area_id', e.target.value)}
                                            error={errors.area_id}
                                            required
                                        >
                                            <option value="">Pilih Area</option>
                                            {areas.map((a) => (
                                                <option key={a.id} value={a.id}>{a.name}</option>
                                            ))}
                                        </Select>
                                        <Input
                                            label="Alamat Singkat"
                                            icon="location_on"
                                            type="text"
                                            value={data.address_short}
                                            onChange={(e) => setData('address_short', e.target.value)}
                                            error={errors.address_short}
                                        />
                                    </div>
                                </Card>
                                <Button type="submit" variant="filled" fullWidth loading={processing} icon="check">
                                    Simpan Perubahan
                                </Button>
                            </form>

                            <div style={{ marginTop: '16px' }}>
                                {(auth?.user?.is_admin || auth?.user?.role === 'admin') && (
                                    <Section title="Manajemen Akun">
                                        <Card variant="outlined">
                                            <div className="md-form-stack">
                                                <Select
                                                    label="Peran"
                                                    icon="badge"
                                                    value={roleForm.data.role}
                                                    onChange={(e) => roleForm.setData('role', e.target.value)}
                                                    error={roleForm.errors.role}
                                                >
                                                    <option value="user">User</option>
                                                    <option value="petugas">Petugas</option>
                                                </Select>
                                                <Button
                                                    variant="tonal"
                                                    fullWidth
                                                    icon="save"
                                                    onClick={handleUpdateRole}
                                                    loading={roleForm.processing}
                                                    disabled={managedUser.id === auth?.user?.id}
                                                >
                                                    Simpan Peran
                                                </Button>
                                            </div>
                                        </Card>
                                    </Section>
                                )}

                                {(auth?.user?.is_admin || auth?.user?.role === 'admin') && managedUser.id !== auth?.user?.id && (
                                    <Section title="Ganti Password">
                                        <Card variant="outlined">
                                            <form onSubmit={handleUpdatePassword} className="md-form-stack">
                                                <Input
                                                    label="Password Baru"
                                                    icon="lock"
                                                    type="password"
                                                    value={passwordForm.data.password}
                                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                                    error={passwordForm.errors.password}
                                                    required
                                                />
                                                <Input
                                                    label="Konfirmasi Password"
                                                    icon="lock"
                                                    type="password"
                                                    value={passwordForm.data.password_confirmation}
                                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                                    required
                                                />
                                                <Button
                                                    type="submit"
                                                    variant="filled"
                                                    fullWidth
                                                    icon="check"
                                                    loading={passwordForm.processing}
                                                >
                                                    Simpan Password
                                                </Button>
                                            </form>
                                        </Card>
                                    </Section>
                                )}
                            </div>
                        </>
                    ) : (
                        <>
                            <Section title="Info Akun">
                                <Card variant="filled">
                                    <div className="md-list-item">
                                        <span className="material-symbols-rounded md-list-item__icon">tag</span>
                                        <div className="md-list-item__content">
                                            <div className="md-label-medium text-muted">ID Pengguna</div>
                                            <div className="md-body-medium">{managedUser.id}</div>
                                        </div>
                                    </div>
                                    <div className="md-list-item">
                                        <span className="material-symbols-rounded md-list-item__icon">verified</span>
                                        <div className="md-list-item__content">
                                            <div className="md-label-medium text-muted">Status</div>
                                            <div className={`md-body-medium ${managedUser.approved_at ? '' : 'text-error'}`}>
                                                {managedUser.approved_at ? 'Aktif' : 'Menunggu Persetujuan'}
                                            </div>
                                        </div>
                                    </div>
                                </Card>
                            </Section>



                            {managedUser.status === 'pending' && managedUser.role === 'user' && (
                                <Section title="Persetujuan Admin">
                                    <Card variant="outlined">
                                        <div className="md-body-small text-muted" style={{ marginBottom: '12px' }}>
                                            Pengguna ini masih menunggu persetujuan admin untuk dapat login.
                                        </div>
                                        <Button
                                            variant="success"
                                            fullWidth
                                            icon="check_circle"
                                            onClick={handleApprove}
                                            loading={approveForm.processing}
                                        >
                                            Setujui Pengguna
                                        </Button>
                                    </Card>
                                </Section>
                            )}

                            <Section title="Zona Bahaya">
                                <Card variant="outlined" style={{ 
                                    borderColor: 'var(--md-sys-color-error)', 
                                    backgroundColor: 'var(--md-sys-color-error-container)' 
                                }}>
                                    <div style={{ color: 'var(--md-sys-color-on-error-container)' }}>
                                        <h4 className="md-title-small" style={{ margin: '0 0 8px' }}>Hapus Akun</h4>
                                        <p className="md-body-small" style={{ margin: '0 0 16px' }}>
                                            Tindakan ini tidak dapat dibatalkan. Semua data pengguna akan dihapus permanen.
                                        </p>
                                        <Button 
                                            variant="danger" 
                                            fullWidth 
                                            icon="delete" 
                                            onClick={() => setShowDeleteModal(true)}
                                        >
                                            Hapus Permanen
                                        </Button>
                                    </div>
                                </Card>
                            </Section>
                        </>
                    )}
                </div>
            </PageContainer>

            <ConfirmModal
                isOpen={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={handleDelete}
                title="Hapus Pengguna"
                message={`Apakah Anda yakin ingin menghapus pengguna "${managedUser.name}"? Tindakan ini tidak dapat dibatalkan.`}
                confirmText="Hapus"
                variant="danger"
            />
        </AppLayout>
    );
}
