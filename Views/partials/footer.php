<?php
$esPeticionAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($esPeticionAjax) {
    return;
}
?>
</main>

<?php if ($haySesion ?? false): ?>
</div>
<script src="/js/app.js" defer></script>
<?php endif; ?>

</body>
</html>