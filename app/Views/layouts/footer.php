</div> <!-- پایان کانتینر main-wrapper -->

<footer class="footer mt-auto py-3 bg-white border-top shadow-sm text-secondary">
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-6 text-md-start mb-2 mb-md-0">
                <small class="fw-bold">&copy; <?= date('Y') ?> سیستم مدیریت سمینار</small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">طراحی شده با دقت و عشق ❤️</small>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // اسکریپت‌های سراسری (مثل فوکوس خودکار که قبلاً نوشتیم)
    document.addEventListener("DOMContentLoaded", function() {
        var inputField = document.getElementById('phone');
        if (inputField) {
            inputField.focus();
            document.addEventListener('click', function(e) {
                const isClickable = e.target.closest('a') || e.target.closest('button') || e.target.closest('.btn') || e.target.closest('input');
                if (!isClickable && e.target !== inputField) {
                    inputField.focus();
                }
            });
            inputField.addEventListener('input', function (e) {
                var value = this.value.replace(/[۰-۹]/g, w => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'].indexOf(w));
                this.value = value.replace(/[^0-9]/g, '');
            });
        }
    });
</script>
</body>
</html>