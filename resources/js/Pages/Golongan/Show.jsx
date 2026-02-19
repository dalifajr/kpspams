import { Head, useForm, router, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input from '@/Components/Input';
import Card, { HeroCard } from '@/Components/Card';
import Button, { IconButton } from '@/Components/Button';
import Alert from '@/Components/Alert';
import { ConfirmModal } from '@/Components/Modal';

export default function GolonganShow({ golongan }) {
    const { flash } = usePage().props;
    const [isEditing, setIsEditing] = useState(false);
    const [showFeeForm, setShowFeeForm] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const hasCustomers = (golongan.customers_count || 0) > 0;

    const { data, setData, put, processing } = useForm({
        code: golongan.code,
        name: golongan.name,
    });

    const feeForm = useForm({ label: '', price: '' });

    const handleUpdate = (e) => {
        e.preventDefault();
        put(`/menu/golongan/${golongan.id}`, { onSuccess: () => setIsEditing(false) });
    };

    const handleDelete = () => {
        if (hasCustomers) return;
        router.delete(`/menu/golongan/${golongan.id}`);
    };

    const handleAddFee = (e) => {
        e.preventDefault();
        feeForm.post(`/menu/golongan/${golongan.id}/fees`, { 
            onSuccess: () => {
                setShowFeeForm(false);
                feeForm.reset();
            }
        });
    };

    return (
        <AppLayout>
            <Head title={golongan.name} />
            <PageContainer>
                <TopAppBar 
                    title={golongan.code} 
                    backHref="/menu/golongan"
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
                    {isEditing ? (
                        <form onSubmit={handleUpdate}>
                            <Card variant="elevated" className="mb-4">
                                <div className="md-form-stack">
                                    <Input
                                        label="Kode Golongan"
                                        icon="tag"
                                        type="text"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                        required
                                    />
                                    <Input
                                        label="Nama Golongan"
                                        icon="category"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                </div>
                            </Card>
                            <Button type="submit" variant="filled" fullWidth loading={processing} icon="check">
                                Simpan Perubahan
                            </Button>
                        </form>
                    ) : (
                        <>
                            <HeroCard 
                                title={golongan.name}
                                subtitle={`${golongan.customers_count || 0} Pelanggan Aktif`}
                                icon="category"
                                className="mb-4"
                            />

                            <Section title="Struktur Tarif" className="mb-4">
                                <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                                    {golongan.tariff_levels?.map((level, i) => (
                                        <div key={i} className="md-list-item">
                                            <span className="material-symbols-rounded md-list-item__icon" style={{ color: 'var(--md-sys-color-primary)' }}>
                                                {i === 0 ? 'looks_one' : i === 1 ? 'looks_two' : 'layers'}
                                            </span>
                                            <div className="md-list-item__content">
                                                <div className="md-body-large" style={{ fontWeight: 500 }}>
                                                    Rp {level.price.toLocaleString()}
                                                </div>
                                                <div className="md-body-small text-muted">
                                                    Pemakaian {level.meter_start} - {level.meter_end || '∞'} m³
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                    {(!golongan.tariff_levels || golongan.tariff_levels.length === 0) && (
                                        <div style={{ padding: '16px', textAlign: 'center', color: 'var(--md-sys-color-outline)' }}>
                                            Belum ada struktur tarif
                                        </div>
                                    )}
                                </Card>
                            </Section>

                            <Section 
                                title="Biaya Tambahan"
                                action={
                                    <Button 
                                        variant="text" 
                                        onClick={() => setShowFeeForm(!showFeeForm)}
                                        icon={showFeeForm ? 'close' : 'add'}
                                    >
                                        {showFeeForm ? 'Batal' : 'Tambah'}
                                    </Button>
                                }
                            >
                                {showFeeForm && (
                                    <form onSubmit={handleAddFee}>
                                        <Card variant="filled" className="mb-4">
                                            <div className="md-form-stack">
                                                <Input
                                                    label="Nama Biaya"
                                                    icon="label"
                                                    type="text"
                                                    placeholder="Misal: Biaya Admin"
                                                    value={feeForm.data.label}
                                                    onChange={(e) => feeForm.setData('label', e.target.value)}
                                                    required
                                                />
                                                <Input
                                                    label="Nominal (Rp)"
                                                    icon="payments"
                                                    type="number"
                                                    value={feeForm.data.price}
                                                    onChange={(e) => feeForm.setData('price', e.target.value)}
                                                    required
                                                />
                                            </div>
                                            <Button type="submit" variant="tonal" fullWidth loading={feeForm.processing} icon="check">
                                                Simpan Biaya
                                            </Button>
                                        </Card>
                                    </form>
                                )}

                                <Card variant="outlined" style={{ padding: 0, overflow: 'hidden' }}>
                                    {golongan.non_air_fees?.map((fee) => (
                                        <div key={fee.id} className="md-list-item">
                                            <span className="material-symbols-rounded md-list-item__icon" style={{ color: 'var(--md-sys-color-secondary)' }}>
                                                receipt
                                            </span>
                                            <div className="md-list-item__content">
                                                <div className="md-body-large">{fee.label}</div>
                                                <div className="md-body-small text-muted">Rp {fee.price.toLocaleString()}</div>
                                            </div>
                                            <Link 
                                                href={`/menu/golongan/${golongan.id}/fees/${fee.id}`} 
                                                method="delete" 
                                                as="button"
                                                style={{ 
                                                    background: 'transparent', 
                                                    border: 'none', 
                                                    color: 'var(--md-sys-color-error)',
                                                    cursor: 'pointer',
                                                    padding: '8px'
                                                }}
                                            >
                                                <span className="material-symbols-rounded">delete</span>
                                            </Link>
                                        </div>
                                    ))}
                                    {(!golongan.non_air_fees || golongan.non_air_fees.length === 0) && !showFeeForm && (
                                        <div style={{ padding: '16px', textAlign: 'center', color: 'var(--md-sys-color-outline)' }}>
                                            Tidak ada biaya tambahan
                                        </div>
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
                                    Hapus Golongan
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
                title="Hapus Golongan"
                message={hasCustomers
                    ? `Golongan "${golongan.name}" masih digunakan oleh ${golongan.customers_count} pelanggan. Hapus atau pindahkan pelanggan terlebih dahulu.`
                    : `Apakah Anda yakin ingin menghapus golongan "${golongan.name}"?`
                }
                confirmText="Hapus"
                variant="danger"
                confirmDisabled={hasCustomers}
            />
        </AppLayout>
    );
}
