/**
 * Bloc « Formulaire de devis » dans l'éditeur : simple aperçu, le formulaire est affiché par le serveur.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.blockEditor ) {
		return;
	}
	const el = wp.element.createElement;
	wp.blocks.registerBlockType( 'snex/devis', {
		apiVersion: 3,
		title: 'Formulaire de devis',
		category: 'widgets',
		icon: 'feedback',
		supports: { html: false, multiple: false, reusable: false },
		edit() {
			return el(
				'div',
				wp.blockEditor.useBlockProps( { className: 'devis-placeholder' } ),
				el( 'strong', null, 'Formulaire de demande de devis' ),
				el( 'p', null, 'Trois étapes : coordonnées, projet, documents. Les demandes sont envoyées à l\'adresse e-mail saisie dans Coordonnées. Le formulaire s\'affiche sur le site.' )
			);
		},
		save: () => null,
	} );
} )( window.wp );
