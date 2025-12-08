# Perbandingan Hasil Test Search Engines

## Tanpa Proxy vs Dengan ScraperAPI

| Mesin Pencari | Tanpa Proxy | Dengan ScraperAPI | Catatan |
|---------------|-------------|-------------------|---------|
| **Google** | ❌ FAILED (0) | ✅ OK (3) | ScraperAPI berhasil bypass |
| **Bing** | ✅ OK (3) | ❌ FAILED (500) | Langsung lebih baik |
| **DuckDuckGo** | ✅ OK (3) | ✅ OK (3) | Stabil keduanya |
| **Yahoo** | ✅ OK (3) | ✅ OK (3) | Stabil keduanya |
| **Mojeek** | ✅ OK (3) | ✅ OK (3) | Stabil keduanya |
| **Brave** | ❌ FAILED (captcha) | ❌ FAILED (captcha) | Perlu perbaikan parser |

## Rekomendasi

### Mesin Pencari Stabil (Tanpa Proxy)
- DuckDuckGo ⭐
- Yahoo ⭐
- Mojeek ⭐
- Bing

### Butuh ScraperAPI
- Google (wajib proxy)

### Perlu Investigasi
- Brave (captcha meski dengan proxy)

## Kecepatan Response

| Mesin Pencari | Tanpa Proxy | Dengan ScraperAPI |
|---------------|-------------|-------------------|
| Google | 484ms | 2,155ms |
| Bing | 348ms | 172,438ms (timeout) |
| DuckDuckGo | 801ms | 2,106ms |
| Yahoo | 1,038ms | 3,753ms |
| Mojeek | 1,940ms | 2,056ms |
| Brave | 628ms | 5,910ms |

**Catatan:** ScraperAPI lebih lambat karena routing melalui proxy server.
