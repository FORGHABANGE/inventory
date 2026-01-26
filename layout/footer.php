<!-- footer.php -->
<footer class="footer">
    <div class="footer-content">
        <marquee behavior="scroll" direction="left" scrollamount="3">
            © <?= date('Y'); ?> Inventory System — All Rights Reserved | Powered by Modern Tech | Secure & Reliable
        </marquee>
    </div>
</footer>

<style>
.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: linear-gradient(90deg, #1f1f1f, #333);
    color: #fff;
    padding: 10px 0;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.3);
    z-index: 1000;
}

.footer-content {
    text-align: center;
    max-width: 100%;
    overflow: hidden;
}

.footer marquee {
    font-size: 14px;
    font-weight: 300;
    color: #ccc;
}
</style>

<!-- Local JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
