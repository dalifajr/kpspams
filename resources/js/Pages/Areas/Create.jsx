import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar } from '@/Layouts/AppLayout';
import Input, { Textarea } from '@/Components/Input';
import Card from '@/Components/Card';
import Button from '@/Components/Button';

export default function AreaCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        customer_count: 0,
        notes: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/menu/area');
    };

    return (
        <AppLayout>
            <Head title="Area Baru" />
            <PageContainer>
                <TopAppBar title="Area Baru" backHref="/menu/area" />

                <div style={{ padding: '0 16px' }}>
                    <form onSubmit={handleSubmit}>
                        <Card variant="elevated" className="mb-4">
                            <div className="md-form-stack">
                                <Input
                                    label="Nama Area"
                                    icon="location_on"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Contoh: Blok A"
                                    error={errors.name}
                                    required
                                />

                                <Input
                                    label="Estimasi Pelanggan"
                                    icon="groups"
                                    type="number"
                                    value={data.customer_count}
                                    onChange={(e) => setData('customer_count', e.target.value)}
                                    error={errors.customer_count}
                                />

                                <Textarea
                                    label="Catatan"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Catatan tambahan (opsional)"
                                    error={errors.notes}
                                    rows={3}
                                />
                            </div>
                        </Card>

                        <div className="flex gap-3 justify-end">
                            <Button variant="text" href="/menu/area">Batal</Button>
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
