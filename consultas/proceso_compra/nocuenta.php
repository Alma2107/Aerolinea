<?php
session_start();
$pageStyles = ['../../css/proceso_compra/pago.css'];
include_once '../../includes/header.php';
?>

<div class="contenedor-pago">
	<div class="main-content">
		<div class="card auth-card">
			<p class="eyebrow">Cuenta requerida</p>
			<h2>Para pagar tenés que iniciar sesión o registrarte</h2>
			<p class="section-copy">Si ya tenés una cuenta, usá Iniciar Sesión. Si todavía no te registraste, usá Registrarse para seguir con la compra.</p>
			<p class="auth-help"><strong>Usá Iniciar Sesión</strong> si ya tenés usuario. <strong>Usá Registrarse</strong> si es tu primera vez.</p>

			<div class="auth-actions">
				<a href="../login/login.php" class="btn-pagar auth-button">Iniciar Sesión</a>
				<a href="../login/registro.php" class="btn-pagar auth-button auth-button-secondary">Registrarse</a>
			</div>

			<p class="auth-note">Cuando termines, vas a volver al paso de pago para completar la reserva.</p>
		</div>
	</div>

	<aside class="sidebar-resumen-final">
		<h3>Qué hacer según tu caso</h3>
		<p>Si ya estás registrado, iniciá sesión y vas a volver al pago automáticamente.</p>
		<hr class="separador-verde">
		<p>Si todavía no tenés cuenta, registrate primero y después continuás con normalidad.</p>
	</aside>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>