        </div>
    </main>

    <!-- ========================================== -->
    <!-- FOOTER -->
    <!-- ========================================== -->
    <footer style="background:#2d3436;color:white;padding:1.5rem 2rem 1rem;margin-left:220px;margin-top:2rem;">
        <div style="max-width:1400px;margin:0 auto;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;margin-bottom:1.5rem;">
                <div>
                    <h4 style="margin-bottom:0.5rem;color:#dfe6e9;font-size:1rem;">📚 Biblioteca Virtual</h4>
                    <p style="color:#b2bec3;font-size:0.85rem;">Sistema de gestión bibliotecaria moderno.</p>
                </div>
                <div>
                    <h4 style="margin-bottom:0.5rem;color:#dfe6e9;font-size:1rem;">Enlaces</h4>
                    <ul style="list-style:none;padding:0;">
                        <li><a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>" style="color:#b2bec3;font-size:0.85rem;text-decoration:none;transition:color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#b2bec3'">Inicio</a></li>
                        <li><a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/resources/index.php" style="color:#b2bec3;font-size:0.85rem;text-decoration:none;transition:color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#b2bec3'">Recursos</a></li>
                        <li><a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/prestamos/index.php" style="color:#b2bec3;font-size:0.85rem;text-decoration:none;transition:color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#b2bec3'">Préstamos</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom:0.5rem;color:#dfe6e9;font-size:1rem;">Contacto</h4>
                    <p style="color:#b2bec3;font-size:0.85rem;">📧 soporte@biblioteca.com</p>
                    <p style="color:#b2bec3;font-size:0.85rem;">📱 (809) 555-0000</p>
                </div>
            </div>
            <div style="border-top:1px solid #636e72;padding-top:1rem;display:flex;justify-content:space-between;color:#b2bec3;font-size:0.8rem;flex-wrap:wrap;gap:0.5rem;">
                <p>&copy; <?php echo date('Y'); ?> Biblioteca Virtual - Todos los derechos reservados</p>
                <p>Versión 1.0.0</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Cerrar sidebar al hacer clic fuera
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            if (window.innerWidth <= 992) {
                if (sidebar && toggle && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    closeSidebar();
                }
            }
        });
    </script>
</body>
</html>