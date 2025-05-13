            </main>
        </div>
    </div>
    <footer class="bg-dark text-white text-center p-4 mt-4">
	<div class="container mt-4">
    	<?php if (is_active_sidebar('footer-1')) : ?>
        	<div class="row">
            	<div class="col">
                	<?php dynamic_sidebar('footer-1'); ?>
            	</div>
        	</div>
    	<?php endif; ?>
	</div>
        <div class="container mt-4">
        <?php if (is_active_sidebar('footer-2')) : ?>
                <div class="row">
                <div class="col">
                        <?php dynamic_sidebar('footer-2'); ?>
                </div>
                </div>
        <?php endif; ?>
        </div>
        <div class="container mt-4">
        <?php if (is_active_sidebar('footer-3')) : ?>
                <div class="row">
                <div class="col">
                        <?php dynamic_sidebar('footer-3'); ?>
                </div>
                </div>
        <?php endif; ?>
        </div>
        <div class="container mt-4">
        <?php if (is_active_sidebar('footer-4')) : ?>
                <div class="row">
                <div class="col">
                        <?php dynamic_sidebar('footer-4'); ?>
                </div>
                </div>
        <?php endif; ?>
        </div>


	<p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php wp_footer(); ?>
</body>
</html>

