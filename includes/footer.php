    </div>
    <footer class="app-footer bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1">&copy; <?= date('Y'); ?> Youth Ministry. All rights reserved.</p>
            <p class="mb-0">Developed by <a class="text-white fw-semibold" href="https://github.com/kimeudan05" target="_blank" rel="noopener">Dante Tech Solutions</a></p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?= htmlspecialchars($assetBase); ?>assets/js/scripts.js"></script>
    <?php if (!empty($extraScripts ?? [])): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
