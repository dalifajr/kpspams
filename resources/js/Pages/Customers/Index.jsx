import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageContainer, TopAppBar, EmptyState } from '@/Layouts/AppLayout';
import { SearchBar } from '@/Components/Input';
import Card from '@/Components/Card';
import { FAB } from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function CustomersIndex({ customers = [], stats = {} }) {
    const { flash } = usePage().props;
    const [searchQuery, setSearchQuery] = useState('');

    const handleSearch = (value) => {
        router.get('/menu/data-pelanggan', { q: value }, { preserveState: true });
    };

    return (
        <AppLayout>
            <Head title="Data Pelanggan" />
            <PageContainer>
                <TopAppBar 
                    title="Pelanggan" 
                    backHref="/dashboard"
                    actions={
                        <span className="md-body-small text-muted">{stats.total || customers.length} Total</span>
                    }
                />

                <div style={{ padding: '0 16px' }}>
                    {flash?.success && (
                        <Alert type="success" className="mb-4">{flash.success}</Alert>
                    )}

                    <SearchBar
                        value={searchQuery}
                        onChange={setSearchQuery}
                        onSearch={handleSearch}
                        placeholder="Cari nama, kode, alamat..."
                        className="mb-4"
                    />

                    {customers.length > 0 ? (
                        <Card variant="outlined">
                            {customers.map((customer) => (
                                <Link 
                                    key={customer.id} 
                                    href={`/menu/data-pelanggan/${customer.id}`} 
                                    className="md-list-item" 
                                    style={{ alignItems: 'flex-start' }}
                                >
                                    <div className="md-list-item__leading tertiary" style={{ marginTop: '4px' }}>
                                        {customer.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="md-list-item__content">
                                        <div className="md-list-item__headline">{customer.name}</div>
                                        <div className="md-list-item__supporting-text" style={{ marginBottom: '8px' }}>
                                            {customer.customer_code}
                                        </div>
                                        <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                                            <span className="md-badge sm">{customer.area?.name || 'No Area'}</span>
                                            <span className="md-badge sm secondary">{customer.golongan?.name || '-'}</span>
                                        </div>
                                    </div>
                                    <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
                                </Link>
                            ))}
                        </Card>
                    ) : (
                        <EmptyState
                            icon="person_off"
                            title="Tidak Ada Pelanggan"
                            message="Tambahkan pelanggan untuk mulai mengelola data"
                        />
                    )}
                </div>

                <FAB icon="person_add" href="/menu/data-pelanggan/create" />
            </PageContainer>
        </AppLayout>
    );
}
