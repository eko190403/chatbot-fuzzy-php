from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
from rapidfuzz import fuzz
import re
import os
import random
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# --- CONFIG from environment variables ---
DB_CONFIG = {
    "host": os.getenv("DB_HOST", "localhost"),
    "user": os.getenv("DB_USER", "root"),
    "password": os.getenv("DB_PASS", ""),
    "database": os.getenv("DB_NAME", "chat_sistem"),
    "cursorclass": pymysql.cursors.DictCursor
}
THRESHOLD = 85  # minimal skor dianggap cocok

# --- Variasi Respons untuk Improvisasi ---
GREETINGS_PREFIX = [
    "Hai! ",
    "Halo! ",
    "Hi kak! ",
    "Oke, ",
    "Baik, ",
    "Tentu! ",
    "Siap! ",
    "Dengan senang hati! ",
    "",
    "",  # Kadang tanpa sapaan
]

CONFIDENT_PREFIX = [
    "Jadi begini, ",
    "Untuk pertanyaan itu, ",
    "Mengenai hal tersebut, ",
    "Nah, ",
    "Oke jadi ",
    "Begini ya, ",
    "",
    "",
]

CLOSING_SUFFIX = [
    " 😊",
    " ✨",
    " 👍",
    " 🙂",
    "",
    "",
    " Semoga membantu ya!",
    " Ada pertanyaan lain?",
    " Kalau masih bingung, tanya lagi ya!",
    "",
]

FALLBACK_RESPONSES = [
    "🤔 Hmm, saya belum punya info tentang itu. Coba tanya dengan kata kunci lain ya!",
    "😅 Maaf, pertanyaan ini belum ada di database saya. Mungkin bisa coba hubungi admin langsung?",
    "🙏 Waduh, saya belum tahu jawabannya. Coba formulasikan pertanyaannya dengan cara lain?",
    "❓ Pertanyaan menarik! Tapi sayangnya saya belum punya jawabannya. Coba tanya yang lain dulu?",
    "🤷 Saya belum bisa jawab yang ini. Mau coba tanya hal lain?",
]


# --- Fungsi Improvisasi Respons ---
def improvisasi_jawaban(jawaban_asli, score):
    """
    Membuat respons lebih natural dengan menambahkan variasi
    tapi tetap mempertahankan informasi asli dari database
    """
    # Jika score sangat tinggi (exact match), beri respons confident
    if score >= 95:
        prefix = random.choice(CONFIDENT_PREFIX)
    else:
        prefix = random.choice(GREETINGS_PREFIX)
    
    suffix = random.choice(CLOSING_SUFFIX)
    
    # Jangan tambahkan prefix jika jawaban sudah dimulai dengan sapaan
    jawaban_lower = jawaban_asli.lower()
    if any(jawaban_lower.startswith(s.lower().strip()) for s in ["hai", "halo", "hi", "oke", "baik", "tentu", "jadi"]):
        prefix = ""
    
    # Jangan tambahkan emoji suffix jika jawaban sudah ada emoji di akhir
    if any(jawaban_asli.strip().endswith(e) for e in ["😊", "✨", "👍", "🙂", "!", "?"]):
        suffix = ""
    
    return f"{prefix}{jawaban_asli}{suffix}"


def get_fallback_response():
    """Mendapatkan respons fallback yang bervariasi"""
    return random.choice(FALLBACK_RESPONSES)


# --- Deteksi Sapaan/Salam ---
def is_greeting(text):
    """Cek apakah input adalah sapaan murni (bukan bagian dari pertanyaan)"""
    text_lower = text.lower().strip()
    
    # List sapaan murni
    pure_greetings = ["halo", "hai", "hi", "hey", "hello", "p", "haii", "haloo", "helloo",
                      "assalamualaikum", "assalamu alaikum", "selamat pagi", "selamat siang",
                      "selamat sore", "selamat malam", "pagi", "siang", "sore", "malam",
                      "pagi kak", "siang kak", "sore kak", "malam kak", "hai kak", "halo kak"]
    
    # Cek apakah teks HANYA berisi sapaan (atau sangat mirip)
    if text_lower in pure_greetings:
        return True
    
    # Cek apakah dimulai dengan sapaan tapi diikuti kata tanya -> bukan sapaan murni
    question_words = ["apa", "siapa", "kapan", "dimana", "bagaimana", "kenapa", "mengapa",
                      "berapa", "gimana", "mana", "apakah", "bisakah", "bisa", "boleh"]
    
    for qw in question_words:
        if qw in text_lower:
            return False
    
    # Cek apakah hanya sapaan + emoticon/punctuation
    cleaned = re.sub(r'[^\w\s]', '', text_lower).strip()
    if cleaned in pure_greetings:
        return True
        
    return False


def respond_greeting():
    """Respons untuk sapaan"""
    responses = [
        "Halo! 👋 Ada yang bisa saya bantu?",
        "Hai kak! 😊 Silakan tanya apa saja!",
        "Hi! Saya siap membantu. Mau tanya apa?",
        "Halo! Selamat datang! Ada pertanyaan seputar kampus?",
        "Hai! 🙌 Silakan ketik pertanyaanmu!",
        "Hello! Mau tahu info apa hari ini?",
    ]
    return random.choice(responses)


def is_thank_you(text):
    """Cek apakah input adalah ucapan terima kasih"""
    thanks = ["terima kasih", "terimakasih", "makasih", "thanks", "thank you", 
              "thx", "tq", "trims", "tengkyu", "ok terima kasih", "oke makasih"]
    return any(t in text.lower() for t in thanks)


def respond_thank_you():
    """Respons untuk terima kasih"""
    responses = [
        "Sama-sama! 😊 Senang bisa membantu!",
        "Sama-sama kak! Jangan sungkan bertanya lagi ya!",
        "You're welcome! 🙌 Semoga harimu menyenangkan!",
        "Siap! Kalau ada pertanyaan lain, langsung tanya aja!",
        "Sama-sama! ✨ Sukses terus ya!",
    ]
    return random.choice(responses)


def is_goodbye(text):
    """Cek apakah input adalah pamitan"""
    goodbyes = ["bye", "dadah", "selamat tinggal", "sampai jumpa", 
                "wassalam", "bye bye", "goodbye", "pamit dulu"]
    return any(g in text.lower() for g in goodbyes)


def respond_goodbye():
    """Respons untuk pamitan"""
    responses = [
        "Sampai jumpa! 👋 Semoga sukses!",
        "Bye! Jangan lupa mampir lagi ya! 😊",
        "Dadah! Semoga harimu menyenangkan! ✨",
        "Sampai ketemu lagi! Good luck! 🍀",
    ]
    return random.choice(responses)


# --- Fungsi Normalisasi Teks ---
def normalisasi(teks):
    teks = teks.lower()
    teks = re.sub(r'[^\w\s]', '', teks)
    teks = ' '.join(teks.split())
    return teks


# --- Koneksi Database ---
def get_db_connection():
    try:
        return pymysql.connect(**DB_CONFIG)
    except pymysql.Error as e:
        print(f"[ERROR] Database connection failed: {e}")
        return None


# --- Route: Health Check ---
@app.route('/', methods=['GET'])
def index():
    return jsonify({
        "status": "OK",
        "message": "Chatbot API is running!",
        "endpoints": {
            "/ask": "POST - Send question to chatbot",
            "/health": "GET - Check API health"
        }
    })


@app.route('/health', methods=['GET'])
def health():
    conn = get_db_connection()
    if conn:
        conn.close()
        return jsonify({"status": "healthy", "database": "connected"})
    else:
        return jsonify({"status": "unhealthy", "database": "disconnected"}), 500


@app.route('/ask', methods=['POST'])
def ask():
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({"response": "Request body kosong.", "score": 0}), 400
            
        user_question_raw = data.get('question', '')
        user_question = normalisasi(user_question_raw)
        kategori = data.get('kategori', '').lower().strip()

        # Validasi input
        if not user_question:
            return jsonify({"response": "Pertanyaan kosong. Silakan ketik sesuatu.", "score": 0})

        if len(user_question) <= 2:
            # Cek apakah itu sapaan singkat seperti "hi" atau "p"
            if is_greeting(user_question):
                return jsonify({"response": respond_greeting(), "score": 100})
            return jsonify({"response": "Maaf, pertanyaan terlalu pendek. Coba lebih spesifik ya!", "score": 0})
        
        # Cek sapaan
        if is_greeting(user_question):
            return jsonify({"response": respond_greeting(), "score": 100})
        
        # Cek terima kasih
        if is_thank_you(user_question):
            return jsonify({"response": respond_thank_you(), "score": 100})
        
        # Cek pamitan
        if is_goodbye(user_question):
            return jsonify({"response": respond_goodbye(), "score": 100})

        # Koneksi database
        connection = get_db_connection()
        if not connection:
            return jsonify({"response": "Maaf, server sedang bermasalah. Coba lagi nanti.", "score": 0}), 500

        # Ambil data pertanyaan dari database
        with connection.cursor() as cursor:
            if kategori:
                cursor.execute("SELECT pertanyaan, jawaban FROM chatbot WHERE LOWER(kategori) = %s", (kategori,))
            else:
                cursor.execute("SELECT pertanyaan, jawaban FROM chatbot")
            rows = cursor.fetchall()
        connection.close()

        best_match = {"question": "", "answer": ""}
        best_score = 0

        # Cari pertanyaan dengan skor tertinggi
        for row in rows:
            pertanyaan_db = row["pertanyaan"]
            jawaban_db = row["jawaban"]

            if not pertanyaan_db:
                continue

            questions = pertanyaan_db.split('|')
            for q in questions:
                q_norm = normalisasi(q)

                if not q_norm:
                    continue

                score = max(
                    fuzz.partial_ratio(user_question, q_norm),
                    fuzz.token_sort_ratio(user_question, q_norm),
                    fuzz.token_set_ratio(user_question, q_norm)
                )

                # Logging untuk debug
                print(f"[DEBUG] User: '{user_question}' | DB: '{q_norm}' | Score: {score}")

                if score > best_score:
                    best_score = score
                    best_match = {"question": q, "answer": jawaban_db}

        # Hasil
        if best_score >= THRESHOLD:
            # Gunakan improvisasi untuk membuat respons lebih natural
            respons_natural = improvisasi_jawaban(best_match["answer"], best_score)
            return jsonify({
                "response": respons_natural,
                "matched_question": best_match["question"],
                "score": best_score
            })
        else:
            return jsonify({
                "response": get_fallback_response(),
                "matched_question": best_match["question"],
                "score": best_score
            })
    
    except Exception as e:
        print(f"[ERROR] Exception in /ask: {e}")
        return jsonify({
            "response": "Terjadi kesalahan server. Silakan coba lagi.",
            "error": str(e),
            "score": 0
        }), 500


if __name__ == '__main__':
    print("🤖 Chatbot API Starting...")
    print("📍 Running on http://127.0.0.1:5000")
    print("📍 Endpoints: /, /health, /ask")
    app.run(debug=True, host='0.0.0.0', port=5000)
