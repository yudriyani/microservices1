const productModel = require('../models/productModel');

// validasi base64 image
function validateBase64Image(image) {
    // 1. image wajib diisi
    if (!image || typeof image !== 'string'){
        return {
            valid: false,
            message: 'Field image wajib diisi'
        };
    }
    
// 2. validasi format base64
    const base64Regex = /^[A-Za-z0-9+/]+={0,2}$/;

    if (!base64Regex.test(image) || image.length % 4 !== 0) {
        return {
            valid: false,
            message: 'Field image harus berupa Base64 yang valid'
        };
    }

// 3. decode base64
        const buffer = Buffer.from(image, 'base64');

// 4. validasi ukuran image maksimal 2MB
   const maxSize = 2 * 1024 * 1024;
   if (buffer.length > maxSize) {
        return {
            valid: false,
            message: 'Ukuran image maksimal 2 MB'
        };
    }

    return {
        valid: true,
    };
}

// GET ambil semua products
async function index(req, res) {
    try {
        const products = await productModel.getAllProducts();
        res.status(200).json({
            message: 'Berhasil mengambil data produk',
            data: products
        });
    } catch (error) {
        res.status(500).json({ 
            message: 'Gagal mengambil data produk',
            error: error.message 
        });
    }
}

// GET ambil produk berdasarkan ID
async function show(req, res) {
    try {
        const id = Number(req.params.id);
        if (!Number.isInteger(id) || id <= 0) {
            return res.status(400).json({ message: 'ID produk tidak valid' });
        }
        const product = await productModel.getProductById(id);
        if (!product) {
            return res.status(404).json({ message: 'Produk tidak ditemukan' });
        }
        res.status(200).json({
            message: 'Berhasil mengambil detail produk',
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal mengambil detail produk',
            error: error.message
        });
    }
}

// POST tambah produk
async function createProduct(req, res) {
    try {
        const { name, description, price, stock, image } = req.body;
        if (!name || price === undefined || stock === undefined) {
            return res.status(400).json({ message: 'Field name, price, dan stock wajib diisi' });
        }

// Validasi image
const validationResult = validateBase64Image(image);
    if (!validationResult.valid) {
        return res.status(400).json({ message: validationResult.message });
    }


// Simpan produk 
const product = await productModel.createProduct({ name, description: description || null, price, stock, image });
        res.status(201).json({
            message: 'Berhasil menambah data produk',
            data: product
        });

} catch (error) {
        res.status(500).json({ 
            message: 'Gagal menambah data produk',
            error: error.message 
        });
    }
}


// PUT update produk 
async function updateProduct(req, res) {
    try {
        const id = Number(req.params.id);
        if (!Number.isInteger(id) || id <= 0) {
            return res.status(400).json({ message: 'ID produk tidak valid' });
        }
        const { name, description, price, stock, image } = req.body;
        if (!name || price === undefined || stock === undefined) {
            return res.status(400).json({ message: 'Field name, price, dan stock wajib diisi' });
        }
        const validationResult = validateBase64Image(image);
        if (!validationResult.valid) {
            return res.status(400).json({ message: validationResult.message });
        }
        const existing = await productModel.getProductById(id);
        if (!existing) {
            return res.status(404).json({ message: 'Produk tidak ditemukan' });
        }
        const product = await productModel.updateProduct(id, { name, description: description || null, price, stock, image });
        res.status(200).json({
            message: 'Berhasil memperbarui data produk',
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal memperbarui data produk',
            error: error.message
        });
    }
}

// DELETE hapus produk
async function destroy(req, res) {
    try {
        const id = Number(req.params.id);
        if (!Number.isInteger(id) || id <= 0) {
            return res.status(400).json({ message: 'ID produk tidak valid' });
        }
        const deleted = await productModel.deleteProduct(id);
        if (!deleted) {
            return res.status(404).json({ message: 'Produk tidak ditemukan' });
        }
        res.status(200).json({ message: 'Berhasil menghapus data produk' });
    } catch (error) {
        res.status(500).json({
            message: 'Gagal menghapus data produk',
            error: error.message
        });
    }
}

module.exports = {
    index,
    show,
    createProduct,
    updateProduct,
    destroy
};
