/**
 * Dissuade la copie du contenu du site : pas de sélection du texte, pas de copier ni de couper
 * (Ctrl+C, Ctrl+X, menu), pas de clic droit, pas de glisser-déposer des images ou du texte.
 * Les champs de formulaire (devis) restent utilisables normalement, copier-coller compris.
 * Les comptes connectés (administrateurs, rédacteurs) ne sont pas concernés.
 * L'appui long sur mobile est bloqué par la feuille de style.
 */
( function () {
	if ( document.body.classList.contains( 'logged-in' ) ) {
		return;
	}
	const isField = ( event ) =>
		event.target instanceof Element && event.target.closest( 'input, textarea, select, [contenteditable="true"]' );
	for ( const type of [ 'contextmenu', 'dragstart', 'selectstart', 'copy', 'cut' ] ) {
		document.addEventListener( type, ( event ) => {
			if ( ! isField( event ) ) {
				event.preventDefault();
			}
		} );
	}
} )();
