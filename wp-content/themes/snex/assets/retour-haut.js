/**
 * Bouton « Remonter en haut de la page » : apparaît après un peu de défilement,
 * ramène en haut de la page (sans animation si le visiteur a demandé moins de mouvements).
 */
( function () {
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'retour-haut';
	button.setAttribute( 'aria-label', 'Remonter en haut de la page' );
	button.title = 'Remonter en haut de la page';
	document.body.append( button );

	const toggle = () => button.classList.toggle( 'is-visible', window.scrollY > 600 );
	window.addEventListener( 'scroll', toggle, { passive: true } );
	toggle();

	button.addEventListener( 'click', () => {
		const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
		const logo = document.querySelector( 'header a' );
		if ( logo ) {
			logo.focus( { preventScroll: true } );
		}
	} );
} )();
