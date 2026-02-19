import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, Section } from '@/Layouts/AppLayout';
import Input, { Select } from '@/Components/Input';
import Card from '@/Components/Card';
import Button from '@/Components/Button';

export default function CustomerCreate({ areas = [], golongans = [], defaultCode }) {
    const { data, setData, post, processing, errors } = useForm({
        customer_code: defaultCode,
        name: '',
        address_short: '',
        phone_number: '',
        area_id: '',
        golongan_id: '',
        family_members: 4,
        meter_reading: 0,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/menu/data-pelanggan');
    };

    return (
        <AppLayout>
            <Head title="Pelanggan Baru" />
            <PageContainer>
                <TopAppBar title="Pelanggan Baru" backHref="/menu/data-pelanggan" />

                <div style={{ padding: '0 16px' }}>
                    <form onSubmit={handleSubmit}>
                        <Card variant="elevated" className="mb-4">
                            <Section title="Data Pelanggan">
                                <div className="md-form-stack">
                                    <Input
                                        label="Kode Pelanggan"
                                        icon="tag"
                                        type="text"
                                        value={data.customer_code}
                                        onChange={(e) => setData('customer_code', e.target.value)}
                                        error={errors.customer_code}
                                        required
                                    />

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
                                        label="Alamat Singkat"
                                        icon="location_on"
                                        type="text"
                                        value={data.address_short}
                                        onChange={(e) => setData('address_short', e.target.value)}
                                        error={errors.address_short}
                                        required
                                    />

                                    <Input
                                        label="Nomor HP (Opsional)"
                                        icon="phone"
                                        type="text"
                                        value={data.phone_number}
                                        onChange={(e) => setData('phone_number', e.target.value)}
                                        placeholder="08xxxxxxxxxx"
                                        error={errors.phone_number}
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
                                        {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                    </Select>

                                    <Select
                                        label="Golongan Tarif"
                                        icon="category"
                                        value={data.golongan_id}
                                        onChange={(e) => setData('golongan_id', e.target.value)}
                                        error={errors.golongan_id}
                                        required
                                    >
                                        <option value="">Pilih Golongan</option>
                                        {golongans.map(g => <option key={g.id} value={g.id}>{g.name}</option>)}
                                    </Select>
                                </div>
                            </Section>

                            <Section title="Data Tambahan">
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                    <Input
                                        label="Anggota Keluarga"
                                        icon="groups"
                                        type="number"
                                        value={data.family_members}
                                        onChange={(e) => setData('family_members', e.target.value)}
                                        error={errors.family_members}
                                    />

                                    <Input
                                        label="Meter Awal (m³)"
                                        icon="speed"
                                        type="number"
                                        value={data.meter_reading}
                                        onChange={(e) => setData('meter_reading', e.target.value)}
                                        step="0.1"
                                        error={errors.meter_reading}
                                    />
                                </div>
                            </Section>
                        </Card>

                        <div className="flex gap-3 justify-end">
                            <Button variant="text" href="/menu/data-pelanggan">Batal</Button>
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
