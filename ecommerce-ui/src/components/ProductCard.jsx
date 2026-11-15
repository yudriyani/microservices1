// src/components/ProductCard.jsx
// Satu kartu produk di halaman daftar.

import { Link } from 'react-router-dom'
import { formatRupiah } from '../utils/format'
import { getProductImage, hideBrokenImage } from '../utils/productImage'
import StarRating from './StarRating'

export default function ProductCard({ product }) {
  const { id, name, price, stock, rating } = product
  const image = getProductImage(product)

  return (
    <article className="card">
      <Link to={`/products/${id}`} className={`card-thumb pastel-${id % 4}`} aria-label={name}>
        {name.charAt(0)}
        {image && <img src={image} alt={name} onError={hideBrokenImage} />}
      </Link>
      <h3>
        <Link to={`/products/${id}`}>{name}</Link>
      </h3>
      <p className="price">{formatRupiah(price)}</p>
      <p className="muted">Stok: {stock}</p>
      {rating ? (
        <StarRating average={rating.average_rating} total={rating.total_reviews} />
      ) : (
        <span className="muted">Rating tidak tersedia</span>
      )}
    </article>
  )
}