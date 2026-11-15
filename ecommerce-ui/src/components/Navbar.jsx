// src/components/Navbar.jsx
// Menu navigasi, kolom pencarian, pemilih user, dan tombol keranjang.
// Belum ada login, jadi user dipilih manual.

import { useState } from 'react'
import { NavLink, useNavigate } from 'react-router-dom'

const USERS = [1, 2, 3, 4, 5]

export default function Navbar({ userId, onUserChange }) {
  const [query, setQuery] = useState('')
  const navigate = useNavigate()

  // Pencarian dikirim lewat alamat (/?q=kata), lalu daftar produk menyaringnya.
  function handleSearch(event) {
    event.preventDefault()
    const keyword = query.trim()
    navigate(keyword ? `/?q=${encodeURIComponent(keyword)}` : '/')
  }

  return (
    <header className="navbar">
      <nav>
        <strong>Toko Online</strong>
        <NavLink to="/" end>
          Produk
        </NavLink>
      </nav>

      <form className="search" onSubmit={handleSearch}>
        <input
          type="search"
          placeholder="Cari produk"
          aria-label="Cari produk"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
        />
      </form>

      <div className="navbar-right">
        <label className="user">
          User ID
          <select value={userId} onChange={(e) => onUserChange(Number(e.target.value))}>
            {USERS.map((id) => (
              <option key={id} value={id}>
                User {id}
              </option>
            ))}
          </select>
        </label>
        <NavLink to="/cart" className="cart-button" aria-label="Keranjang">
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <circle cx="9" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
            <path d="M3 4h2l2.4 11.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 8H6" />
          </svg>
        </NavLink>
      </div>
    </header>
  )
}