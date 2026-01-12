<?php
require_once 'session_init.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Regenerate session ID hanya sekali setelah login
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}

require_once 'db.php';

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

if (!$userData) {
    // User tidak ditemukan, hapus session dan redirect
    $_SESSION = array();
    header("Location: login.php");
    exit();
}

$user_id = (int)$userData['id'];
$username = $userData['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AkademikaBot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrfMeta() ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/chatbot.css">
</head>
<body>

<div class="chat-container">
    <div class="chat-navbar">
        <div class="navbar-title">AkademikaBot</div>
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: transparent; color: white; border: none;">
                &#x22EE;
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="chat.php">💬 Live Chat</a></li>
                <li><a class="dropdown-item" href="#" id="hapus-chat">🗑 Hapus Riwayat</a></li>
                <li><a class="dropdown-item" href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="chat-box" id="chat-box">
        <div class="chat-bubble bot-message">
            <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
            Hai, ada yang bisa saya bantu?
        </div>
        <?php
        // Load chat history
        $stmt = $conn->prepare("SELECT * FROM riwayat_chatbot WHERE user_id = ? ORDER BY waktu ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo '<div class="chat-bubble user-message">' . escape($row['pertanyaan_user']) . '</div>';
            echo '<div class="chat-bubble bot-message">
                    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
                    <div>' . nl2br(escape($row['jawaban_bot']));

            if (empty($row['feedback'])) {
                echo '<div class="feedback-buttons">
                        <small>Bermanfaat?</small>
                        <button class="btn btn-sm btn-outline-success ms-2 btn-feedback" data-id="' . (int)$row['id'] . '" data-val="bantu">👍</button>
                        <button class="btn btn-sm btn-outline-danger ms-1 btn-feedback" data-id="' . (int)$row['id'] . '" data-val="tidak">👎</button>
                      </div>';
            } else {
                echo '<div class="feedback-buttons">
                        <span class="text-success small">✔ Feedback diterima</span>
                      </div>';
            }

            echo '</div></div>';
        }
        $stmt->close();
        ?>
    </div>

    <div class="kategori-select">
        <select id="kategori" class="form-select">
            <option value="">Pilih Kategori Pertanyaan</option>
            <?php
            $kategori_query = "SELECT DISTINCT kategori FROM chatbot WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
            $kategori_result = mysqli_query($conn, $kategori_query);
            while ($row = mysqli_fetch_assoc($kategori_result)) {
                echo '<option value="' . escape($row['kategori']) . '">' . ucfirst(escape($row['kategori'])) . '</option>';
            }
            ?>
        </select>
    </div>

    <div id="pertanyaan-wrapper" style="display:none;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <strong>Pertanyaan</strong>
            <button id="tutup-pertanyaan" class="btn btn-sm btn-danger" style="padding: 2px 6px;">❌</button>
        </div>
        <div class="pertanyaan-terdaftar" id="daftar-pertanyaan"></div>
    </div>

    <div class="chat-input">
        <input id="text-pesan" type="text" placeholder="Ketik pesan..." required>
        <button id="send-btn">&#9658;</button>
    </div>
</div>

<script>
function loadPertanyaan(kategori) {
    if (!kategori) {
        $("#pertanyaan-wrapper").hide();
        $("#daftar-pertanyaan").html("");
        return;
    }
    $.post("get_pertanyaan.php", { kategori: kategori }, function (data) {
        $("#daftar-pertanyaan").html(data);
        $("#pertanyaan-wrapper").show();
    });
}

$(document).ready(function () {
    let lastKategori = "";

    $("#kategori").on("change", function () {
        const kategori = $(this).val();
        if (kategori) {
            loadPertanyaan(kategori);
            lastKategori = kategori;
        }
    });

    $(document).on("click", "#tutup-pertanyaan", function () {
        $("#pertanyaan-wrapper").hide();
        $("#daftar-pertanyaan").html("");
    });

    $("#kategori").on("click", function () {
        const kategori = $(this).val();
        if (kategori === lastKategori && kategori !== "") {
            loadPertanyaan(kategori);
        }
    });

    $(document).on("click", ".pertanyaan-item", function () {
        const isi = $(this).text();
        $("#text-pesan").val(isi).focus();
        $("#daftar-pertanyaan").html("");
    });

    $("#send-btn").on("click", sendMessage);
    $("#text-pesan").keypress(function (e) {
        if (e.which === 13) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        const pesan = $("#text-pesan").val().trim();
        const kategori = $("#kategori").val().trim();
        if (!pesan) return;

        $("#chat-box").append('<div class="chat-bubble user-message">' + pesan + '</div>');
        $("#text-pesan").val("");
        $("#chat-box").append('<div class="chat-bubble bot-message typing-indicator" id="loading">Sedang mengetik...</div>');
        $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);

        $.ajax({
            url: "pesan.php",
            type: "POST",
            data: {
                isi_pesan: pesan,
                kategori: kategori
            },
            dataType: "json",
            success: function (res) {
                $("#loading").remove();
                
                // Check if response has error
                if (res.error) {
                    const errorMsg = `
                    <div class="chat-bubble bot-message">
                        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
                        <div style="color: red;">Error: ${res.error}</div>
                    </div>`;
                    $("#chat-box").append(errorMsg);
                    $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
                    return;
                }

                // Check if jawaban exists
                const jawaban = res.jawaban || "Maaf, tidak ada jawaban yang diterima.";
                const chatId = res.id || 0;

                const botMsg = ` 
                <div class="chat-bubble bot-message">
                    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
                    <div>${jawaban}
                        <div class="feedback-buttons">
                            <small>Bermanfaat?</small>
                            <button class="btn btn-sm btn-outline-success ms-2 btn-feedback" data-id="${chatId}" data-val="bantu">👍</button>
                            <button class="btn btn-sm btn-outline-danger ms-1 btn-feedback" data-id="${chatId}" data-val="tidak">👎</button>
                        </div>
                    </div>
                </div>`;
                $("#chat-box").append(botMsg);
                $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
            },
            error: function(xhr, status, error) {
                $("#loading").remove();
                const errorMsg = `
                <div class="chat-bubble bot-message">
                    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
                    <div style="color: red;">Gagal menghubungi server. Silakan coba lagi.</div>
                </div>`;
                $("#chat-box").append(errorMsg);
                $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
                console.error("Error:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
            }
        });
    }

    $(document).on("click", ".btn-feedback", function () {
        const button = $(this);
        const id = button.data("id");
        const feedback = button.data("val");

        $.post("simpan_feedback.php", { id: id, feedback: feedback }, function () {
            button.closest(".feedback-buttons").html('<span class="text-success small">✔ Feedback diterima</span>');
        });
    });

    // 🔥 Tambahan: Hapus Riwayat via AJAX
    $(document).on("click", "#hapus-chat", function (e) {
        e.preventDefault();
        if (confirm("Yakin ingin hapus semua riwayat chat?")) {
            $.ajax({
                url: "hapus_chat.php",
                type: "POST",
                dataType: "json",
                success: function (res) {
                    if (res.status === "success") {
                        $("#chat-box").html(
                            `<div class="chat-bubble bot-message">
                                <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot">
                                Riwayat chat sudah dihapus.
                            </div>`
                        );
                    } else {
                        alert(res.message);
                    }
                },
                error: function () {
                    alert("Terjadi kesalahan saat menghapus chat.");
                }
            });
        }
    });
});
</script>
</body>
</html>
