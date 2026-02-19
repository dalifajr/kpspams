import { Head, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input, { Select, Textarea } from '@/Components/Input';
import Card from '@/Components/Card';
import Button, { IconButton } from '@/Components/Button';
import { ConfirmModal } from '@/Components/Modal';

export default function AreaShow({ area, petugasOptions = [] }) {
    const [isEditing, setIsEditing] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const { data, setData, put, processing } = useForm({
        name: area.name,
        customer_count: area.customer_count,
        notes: area.notes || '',
    });

    const handleUpdate = (e) => {
        e.preventDefault();
        put(`/menu/area/${area.id}`, { onSuccess: () => setIsEditing(false) });
    };

    const handleDelete = () => {
        router.delete(`/menu/area/${area.id}`);
    };

    const handleAddPetugas = (userId) => {
        router.post(`/menu/area/${area.id}/petugas`, { user_id: userId }, { preserveScroll: true });
    };

    const handleRemovePetugas = (userId) => {
        router.delete(`/menu/area/${area.id}/petugas/${userId}`, { preserveScroll: true });
    };

    return (
        <AppLayout>
            <Head title={area.name} />
            <PageContainer>
                <TopAppBar 
                    title="Detail Area" 
                    backHref="/menu/area"
                    actions={
                        <IconButton 
                            icon={isEditing ? 'close' : 'edit'} 
                            onClick={() => setIsEditing(!isEditing)} 
                        />
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {isEditing ? (
                        <form onSubmit={handleUpdate}>
                            <Card variant="elevated" className="mb-4">
                                <div className="md-form-stack">
                                    <Input
                                        label="Nama Area"
                                        icon="location_on"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    <Input
                                        label="Estimasi Pelanggan"
                                        icon="groups"
                                        type="number"
                                        value={data.customer_count}
                                        onChange={(e) => setData('customer_count', e.target.value)}
                                    />
                                    <Textarea
                                        label="Catatan"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        rows={3}
                                    />
                                </div>
                            </Card>
                            <Button type="submit" variant="filled" fullWidth loading={processing} icon="check">
                                Simpan
                            </Button>
                        </form>
                    ) : (
                        <>
                            <Card variant="filled" className="mb-4" style={{ 
                                backgroundColor: 'var(--md-sys-color-secondary-container)', 
                                color: 'var(--md-sys-color-on-secondary-container)' 
                            }}>
                                <div className="md-headline-small" style={{ marginBottom: '8px' }}>{area.name}</div>
                                <div className="md-body-medium">{area.customer_count} Pelanggan (Est)</div>
                                <div className="md-body-small" style={{ opacity: 0.8, marginTop: '4px' }}>
                                    {area.notes || 'Tidak ada catatan'}
                                </div>
                            </Card>

                            <Section title={`Petugas (${area.petugas?.length || 0})`}>
                                <Card variant="outlined">
                                    {area.petugas?.length > 0 ? (
                                        area.petugas.map((petugas) => (
                                            <div key={petugas.id} className="md-list-item">
                                                <div className="md-list-item__leading" style={{ width: 32, height: 32, fontSize: '0.75rem' }}>
                                                    {petugas.name.charAt(0)}
                                                </div>
                                                <div className="md-list-item__content">
                                                    <div className="md-list-item__headline">{petugas.name}</div>
                                                </div>
                                                <IconButton 
                                                    icon="remove_circle" 
                                                    onClick={() => handleRemovePetugas(petugas.id)}
                                                    style={{ color: 'var(--md-sys-color-error)' }}
                                                />
                                            </div>
                                        ))
                                    ) : (
                                        <div className="md-body-medium text-muted" style={{ padding: '16px', fontStyle: 'italic' }}>
                                            Belum ada petugas yang ditugaskan.
                                        </div>
                                    )}

                                    <div style={{ padding: '16px', borderTop: '1px solid var(--md-sys-color-outline-variant)' }}>
                                        <div className="md-label-medium text-muted" style={{ marginBottom: '8px' }}>Tambah Petugas</div>
                                        <div style={{ display: 'flex', gap: '8px' }}>
                                            <select 
                                                id="petugas-chooser" 
                                                style={{ 
                                                    flex: 1, 
                                                    padding: '12px', 
                                                    borderRadius: 'var(--shape-expressive-small)', 
                                                    border: '1px solid var(--md-sys-color-outline)',
                                                    background: 'var(--md-sys-color-surface)',
                                                    fontSize: '1rem'
                                                }}
                                            >
                                                <option value="">Pilih Petugas...</option>
                                                {petugasOptions.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                            </select>
                                            <Button
                                                variant="tonal"
                                                onClick={() => {
                                                    const val = document.getElementById('petugas-chooser').value;
                                                    if (val) handleAddPetugas(val);
                                                }}
                                            >
                                                Tambah
                                            </Button>
                                        </div>
                                    </div>
                                </Card>
                            </Section>

                            <div style={{ textAlign: 'center', marginTop: '24px' }}>
                                <Button 
                                    variant="text" 
                                    onClick={() => setShowDeleteModal(true)}
                                    style={{ color: 'var(--md-sys-color-error)' }}
                                    icon="delete"
                                >
                                    Hapus Area
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
                title="Hapus Area"
                message={`Apakah Anda yakin ingin menghapus area "${area.name}"?`}
                confirmText="Hapus"
                variant="danger"
            />
        </AppLayout>
    );
}
