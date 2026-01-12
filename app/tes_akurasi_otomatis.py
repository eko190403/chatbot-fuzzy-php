import pymysql
import requests
import random
import pandas as pd
from rapidfuzz import fuzz

# --- CONFIG ---
DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = ""
DB_NAME = "chat_system"
API_URL = "http://localhost:5000/ask"
THRESHOLD = 80  # minimal skor kemiripan jawaban benar

# --- Fungsi variasi ---
def buat_typo_ringan(text):
    if len(text) < 4:
        return text
    idx = random.randint(0, len(text) - 2)
    return text[:idx] + text[idx+1:]

def buat_typo_berat(text):
    if len(text) < 6:
        return text
    idx1, idx2 = sorted(random.sample(range(len(text)), 2))
    text = text[:idx1] + text[idx1+1:]
    text = text[:idx2-1] + text[idx2:]
    mapping = {"a": "4", "e": "3", "i": "1", "o": "0", "s": "5"}
    return "".join([mapping.get(ch.lower(), ch) for ch in text])

def buat_sinonim(text):
    mapping = {
        "cara": "metode",
        "cek": "periksa",
        "melihat": "mengecek",
        "nilai": "skor",
        "di": "pada"
    }
    words = text.split()
    return " ".join([mapping.get(w.lower(), w) for w in words])

def ubah_struktur(text):
    words = text.split()
    if len(words) > 3:
        return " ".join(words[-2:] + words[:-2])
    return text

def variasi_pertanyaan(text):
    return [
        ("exact", text),
        ("typo_ringan", buat_typo_ringan(text)),
        ("sinonim", buat_sinonim(text)),
        ("ubah_struktur", ubah_struktur(text)),
        ("typo_berat", buat_typo_berat(text))
    ]

# --- Ambil pertanyaan dari DB ---
conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
cursor = conn.cursor()
cursor.execute("SELECT pertanyaan, jawaban, kategori FROM chatbot")
data = cursor.fetchall()
conn.close()

total = 0
benar = 0
hasil = []

print("🔍 Mulai pengujian akurasi chatbot...\n")

for pertanyaan, jawaban, kategori in data:
    pertanyaan_list = pertanyaan.split("|") if pertanyaan else []

    for p in pertanyaan_list:
        variasi = variasi_pertanyaan(p)

        for tipe, q in variasi:
            if not q:
                continue
            total += 1
            try:
                response = requests.post(API_URL, json={"question": q}).json()
                jawaban_bot = response.get("response", "")
                matched_q = response.get("matched_question", "")
                score_api = response.get("score", 0)

                # Hitung kesamaan jawaban dengan kunci
                skor_jawaban = fuzz.token_set_ratio(jawaban_bot.lower(), jawaban.lower())
                status = "BENAR" if skor_jawaban >= THRESHOLD else "SALAH"
                if status == "BENAR":
                    benar += 1

                print(f"[{status}] [{tipe}] {q} -> {jawaban_bot} | match: {matched_q} | score: {score_api}")

                hasil.append({
                    "Kategori": kategori,
                    "Pertanyaan Original": p,
                    "Tipe Variasi": tipe,
                    "Pertanyaan Uji": q,
                    "Pertanyaan Terpilih Bot": matched_q,
                    "Jawaban Benar": jawaban,
                    "Jawaban Bot": jawaban_bot,
                    "Score Matching Pertanyaan": score_api,
                    "Score Kesesuaian Jawaban": skor_jawaban,
                    "Status": status
                })

            except Exception as e:
                print(f"⚠️ Error kirim pertanyaan: {q} ({e})")

# --- Statistik ---
df = pd.DataFrame(hasil)
stat_kategori = df.groupby("Kategori")["Status"].apply(lambda x: (x == "BENAR").mean() * 100).reset_index()
stat_kategori.columns = ["Kategori", "Akurasi (%)"]

stat_variasi = df.groupby("Tipe Variasi")["Status"].apply(lambda x: (x == "BENAR").mean() * 100).reset_index()
stat_variasi.columns = ["Tipe Variasi", "Akurasi (%)"]

# --- Simpan ke Excel ---
with pd.ExcelWriter("hasil_akurasi_lengkap.xlsx") as writer:
    df.to_excel(writer, sheet_name="Detail", index=False)
    stat_kategori.to_excel(writer, sheet_name="Per Kategori", index=False)
    stat_variasi.to_excel(writer, sheet_name="Per Variasi", index=False)

print("\n--- HASIL AKHIR ---")
print(f"Total Pertanyaan Diuji: {total}")
print(f"Jawaban Benar: {benar}")
print(f"Akurasi Global: {benar / total * 100:.2f}%")
print("✅ Hasil lengkap disimpan di 'hasil_akurasi_lengkap.xlsx'")
