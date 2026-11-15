// src/pages/ProductListPage.jsx
// Halaman daftar produk: data produk dari product-service, rating dari review-service.
// Kata kunci pencarian (?q=...) hanya menyaring tampilan, bukan permintaan ke service.

import { useSearchParams } from 'react-router-dom'
import { getProductsWithRating } from '../api/aggregate'
import AsyncView from '../components/AsyncView'
import ProductCard from '../components/ProductCard'
import { useAsync } from '../hooks/useAsync'

export default function ProductListPage() {
  const { loading, data, error, reload } = useAsync(getProductsWithRating)
  const [searchParams] = useSearchParams()
  const keyword = (searchParams.get('q') ?? '').toLowerCase()

  const products = data ? data.filter((product) => product.name.toLowerCase().includes(keyword)) : []

  return (
    <section>
      <div className="banner">
        <h2>Selamat datang di Toko Online</h2>
        <p>Pilih produk, lihat ulasan, lalu masukkan ke keranjang.</p>
      </div>

      <h1>Produk</h1>
      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && products.length === 0 && <p className="muted">Tidak ada produk yang cocok.</p>}
        {products.length > 0 && (
          <div className="grid">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}
      </AsyncView>
    </section>
  )
}