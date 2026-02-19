import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input from '@/Components/Input';
import Card from '@/Components/Card';
import Button, { IconButton } from '@/Components/Button';

export default function GolonganCreate() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        tariffs: [{ meter_start: 0, meter_end: '', price: 0 }],
    });

    const addTariffLevel = () => {
        setData('tariffs', [...data.tariffs, { meter_start: 0, meter_end: '', price: 0 }]);
    };

    const removeTariffLevel = (index) => {
        const newTariffs = [...data.tariffs];
        newTariffs.splice(index, 1);
        setData('tariffs', newTariffs);
    };

    const updateTariff = (index, field, value) => {
        const newTariffs = [...data.tariffs];
        newTariffs[index][field] = value;
        setData('tariffs', newTariffs);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/menu/golongan');
    };

    return (
        <AppLayout>
            <Head title="Golongan Baru" />
            <PageContainer>
                <TopAppBar title="Golongan Baru" backHref="/menu/golongan" />

                <div style={{ padding: '0 16px' }}>
                    <form onSubmit={handleSubmit}>
                        <Card variant="elevated" className="mb-4">
                            <Section title="Info Dasar">
                                <div className="md-form-stack">
                                    <Input
                                        label="Kode Golongan"
                                        icon="tag"
                                        type="text"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                        placeholder="Misal: R1"
                                        error={errors.code}
                                        required
                                    />
                                    <Input
                                        label="Nama Golongan"
                                        icon="category"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Rumah Tangga Kecil"
                                        error={errors.name}
                                        required
                                    />
                                </div>
                            </Section>
                        </Card>

                        <Card variant="elevated" className="mb-4">
                            <Section 
                                title="Tingkatan Tarif"
                                action={
                                    <Button variant="text" onClick={addTariffLevel} icon="add">
                                        Tambah Level
                                    </Button>
                                }
                            >
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                    {data.tariffs.map((tariff, index) => (
                                        <Card key={index} variant="outlined">
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                                                <span className="md-label-large" style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                    <span className="material-symbols-rounded" style={{ fontSize: '18px', color: 'var(--md-sys-color-primary)' }}>layers</span>
                                                    Level {index + 1}
                                                </span>
                                                {index > 0 && (
                                                    <IconButton 
                                                        icon="delete" 
                                                        onClick={() => removeTariffLevel(index)}
                                                        style={{ color: 'var(--md-sys-color-error)' }}
                                                    />
                                                )}
                                            </div>
                                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                                                <Input
                                                    label="Awal (m³)"
                                                    type="number"
                                                    value={tariff.meter_start}
                                                    onChange={(e) => updateTariff(index, 'meter_start', e.target.value)}
                                                />
                                                <Input
                                                    label="Akhir (m³)"
                                                    type="number"
                                                    value={tariff.meter_end}
                                                    onChange={(e) => updateTariff(index, 'meter_end', e.target.value)}
                                                    placeholder="∞"
                                                />
                                            </div>
                                            <div style={{ marginTop: '12px' }}>
                                                <Input
                                                    label="Harga per m³ (Rp)"
                                                    icon="payments"
                                                    type="number"
                                                    value={tariff.price}
                                                    onChange={(e) => updateTariff(index, 'price', e.target.value)}
                                                />
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            </Section>
                        </Card>

                        <div className="flex gap-3 justify-end">
                            <Button variant="text" href="/menu/golongan">Batal</Button>
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
