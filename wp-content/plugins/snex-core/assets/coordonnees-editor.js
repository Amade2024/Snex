/**
 * Affiche les vraies coordonnées dans l'éditeur à la place du libellé « Coordonnées S-NEX ».
 * Les valeurs se modifient dans Coordonnées (menu d'administration), pas dans les blocs.
 */
( function ( wp, values ) {
	if ( ! wp || ! wp.blocks || ! wp.blocks.registerBlockBindingsSource ) {
		return;
	}
	wp.blocks.registerBlockBindingsSource( {
		name: 'snex/coordonnees',
		label: 'Coordonnées S-NEX',
		getValues( { bindings } ) {
			const result = {};
			Object.keys( bindings ).forEach( ( attribute ) => {
				const args = bindings[ attribute ].args || {};
				result[ attribute ] = ( values && values[ args.key ] ) || '';
			} );
			return result;
		},
		canUserEditValue() {
			return false;
		},
	} );
} )( window.wp, window.snexCoordonnees );
