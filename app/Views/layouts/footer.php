</div>   

<br><br>
<footer class="text-center text-muted py-3 border-top">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var inputField = document.getElementById('phone');

        // ۱. به محض لود شدن صفحه، فوکوس برود روی اینپوت
        inputField.focus();

        // ۲. اگر کاربر جایی دیگر کلیک کرد، دوباره فوکوس برگردد روی اینپوت
        // (این حالت برای وقتی خوب است که سیستم فقط مخصوص اسکن است - حالت کیوسک)
        document.addEventListener('click', function(e) {
            if (e.target !== inputField) {
                inputField.focus();
            }
        });

        // ۳. جلوگیری از تایپ حروف غیر عددی (اگر QR اشتباه اسکن شد)
        inputField.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>
</body>
</html>