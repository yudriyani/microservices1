// src/utils/productImage.js
// Memilih gambar produk dari folder public/images (dilayani Vite dari alamat /images/...).
// Keyboard punya beberapa gambar, jadi dipilih bergantian berdasarkan id produk
// supaya tiap produk terlihat berbeda.

const KEYBOARD_IMAGES = [
  '/images/keyboard2.jpeg',
  '/images/keyboard3.jpeg',
  '/images/keyboard4.jpeg',
  '/images/keyboard5.jpeg',
  '/images/keyboard6.jpeg',
  '/images/keyboard7.jpeg',
  '/images/keyboard8.jpeg',
  '/images/keyboard10.jpeg',
]

// Mau menentukan gambar tepat untuk produk tertentu? Isi di sini, contoh:
// const IMAGE_BY_ID = { 14: '/images/keyboard2.jpeg' }
const IMAGE_BY_ID = {}

export function getProductImage(product) {
  if (IMAGE_BY_ID[product.id]) return IMAGE_BY_ID[product.id]

  const name = product.name.toLowerCase()
  if (name.includes('keyboard')) return KEYBOARD_IMAGES[product.id % KEYBOARD_IMAGES.length]
  if (name.includes('laptop')) return '/images/laptop.jpeg'
  if (name.includes('headset')) return '/images/headset.jpeg'
  if (name.includes('mouse')) return '/images/mouse.jpeg'
  return null
}

// Kalau file gambar tidak ada, sembunyikan gambar. Huruf pastel di bawahnya tetap tampil.
export function hideBrokenImage(event) {
  event.currentTarget.style.display = 'none'
}