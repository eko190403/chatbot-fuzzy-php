// chat_delete.js
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById("chat-box");
    const userId = window.userId || null; // pastikan userId sudah didefinisikan di chat.php

    // Hapus semua chat
    function hapusSemua() {
        if (!userId) return;
        if (confirm("Yakin ingin menghapus semua riwayat chat?")) {
            fetch("hapus_chat_live.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "hapus_semua=1"
            })
            .then(res => res.text())
            .then(res => res.trim())
            .then(res => {
                if (res === "ok") {
                    chatBox.innerHTML = "";
                    alert("Semua chat berhasil dihapus!");
                    // Kalau pakai websocket:
                    if (window.ws) {
                        window.ws.send(JSON.stringify({ type: "delete_all", user_id: userId }));
                    }
                } else {
                    alert("Gagal hapus semua chat!");
                }
            })
            .catch(() => alert("Gagal hapus semua chat!"));
        }
    }

    // Hapus per chat
    function hapusPerChat(bubble, id) {
        if (!id) return;
        if (confirm("Hapus chat ini?")) {
            fetch("hapus_chat_live.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "message_id=" + id
            })
            .then(res => res.text())
            .then(res => res.trim())
            .then(res => {
                if (res === "ok") {
                    bubble.remove();
                    if (window.ws) {
                        window.ws.send(JSON.stringify({ type: "delete_one", message_id: id }));
                    }
                } else {
                    alert("Gagal hapus chat!");
                }
            })
            .catch(() => alert("Gagal hapus chat!"));
        }
    }

    // Expose fungsi supaya bisa dipanggil dari HTML
    window.hapusSemua = hapusSemua;
    window.hapusPerChat = hapusPerChat;
});
