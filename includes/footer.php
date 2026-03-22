    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo t('shop'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('catalog.php'); ?>"><?php echo t('all_deals'); ?></a></li>
                        <li><a href="<?php echo url('catalog.php?category=beverages'); ?>"><?php echo t('new_arrivals'); ?></a></li>
                        <li><a href="<?php echo url('promotions.php'); ?>"><?php echo t('loyalty_rewards'); ?></a></li>
                        <li><a href="<?php echo url('catalog.php'); ?>"><?php echo t('find_store'); ?></a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><?php echo t('support'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="#"><?php echo t('help_center'); ?></a></li>
                        <li><a href="#"><?php echo t('delivery_areas'); ?></a></li>
                        <li><a href="#"><?php echo t('privacy_policy'); ?></a></li>
                        <li><a href="#"><?php echo t('terms_of_service'); ?></a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><?php echo t('follow_us'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><?php echo t('about'); ?></h4>
                    <p style="color: var(--color-gray); font-size: 14px;">
                        <?php echo t('footer_text'); ?>
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> 7-Select E-Commerce. <?php echo t('all_rights_reserved'); ?> | <?php echo t('powered_by'); ?></p>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--color-success);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    ">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Product added to cart!</span>
    </div>

    <script src="<?php echo url('assets/js/main.js'); ?>?v=3"></script>
</body>
</html>
