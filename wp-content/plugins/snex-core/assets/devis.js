/**
 * Formulaire de devis : une étape à la fois, champs vérifiés avant de continuer, récapitulatif avant l'envoi.
 * Sans ce script, les trois étapes restent affichées et le serveur vérifie la saisie (includes/devis.php).
 */
( function () {
	const form = document.querySelector( 'form.devis' );
	if ( ! form ) {
		return;
	}
	const steps = Array.from( form.querySelectorAll( '.devis__step' ) );
	const marks = Array.from( form.querySelectorAll( '.devis__progress li' ) );
	const last = steps.length - 1;
	const maxFiles = Number( form.dataset.maxFiles );
	const maxSize = Number( form.dataset.maxSize );
	const maxTotal = Number( form.dataset.maxTotal );
	const accepted = /\.(pdf|jpe?g|png|webp|docx?|xlsx?)$/i;
	let current = 0;

	const $ = ( selector, root = form ) => root.querySelector( selector );
	const mb = ( bytes ) =>
		bytes < 1048576
			? Math.max( 1, Math.round( bytes / 1024 ) ) + ' Ko'
			: ( bytes / 1048576 ).toLocaleString( 'fr-FR', { maximumFractionDigits: 1 } ) + ' Mo';

	/* ---------- Vérification des champs ---------- */

	function isValidPhone( raw ) {
		const value = raw.trim();
		const digits = value.replace( /\D/g, '' );
		if ( /^(\+|00)/.test( value ) ) {
			const intl = value.startsWith( '00' ) ? digits.slice( 2 ) : digits;
			return intl.startsWith( '225' ) ? intl.length === 13 : intl.length >= 8 && intl.length <= 15;
		}
		return digits.length === 10;
	}

	function filesError( input ) {
		const files = Array.from( input.files || [] );
		if ( files.length > maxFiles ) {
			return `Joignez ${ maxFiles } fichiers au maximum.`;
		}
		for ( const file of files ) {
			if ( ! accepted.test( file.name ) ) {
				return `Format non accepté pour « ${ file.name } ». Formats acceptés : PDF, JPG, PNG, WebP, Word, Excel.`;
			}
			if ( file.size > maxSize ) {
				return `« ${ file.name } » dépasse ${ mb( maxSize ) }.`;
			}
		}
		if ( files.reduce( ( sum, f ) => sum + f.size, 0 ) > maxTotal ) {
			return `L'ensemble des fichiers dépasse ${ mb( maxTotal ) }. Retirez-en un ou réduisez leur taille.`;
		}
		return '';
	}

	function setError( field, message ) {
		const group = field.type === 'radio' ? form.querySelectorAll( `[name="${ field.name }"]` ) : [ field ];
		group.forEach( ( el ) => ( message ? el.setAttribute( 'aria-invalid', 'true' ) : el.removeAttribute( 'aria-invalid' ) ) );
		const box = $( '#devis-err-' + field.name.replace( '[]', '' ) );
		if ( box ) {
			box.textContent = message;
		}
	}

	function messageFor( field ) {
		if ( field.closest( '[hidden]' ) && ! field.closest( '.devis__step[hidden]' ) ) {
			return ''; // Champ conditionnel masqué.
		}
		if ( field.type === 'file' ) {
			return filesError( field );
		}
		if ( field.type === 'radio' ) {
			const first = form.querySelector( `[name="${ field.name }"]` );
			if ( form.querySelector( `[name="${ field.name }"]:checked` ) || ! first.required ) {
				return '';
			}
			return field.name === 'service' ? 'Choisissez le service concerné.' : 'Choisissez une option.';
		}
		if ( field.name === 'telephone' && field.value.trim() && ! isValidPhone( field.value ) ) {
			return field.dataset.error;
		}
		if ( field.required && ! field.value.trim() ) {
			return field.dataset.error || 'Ce champ est obligatoire.';
		}
		return field.checkValidity() ? '' : field.dataset.error || field.validationMessage;
	}

	function fieldsOf( step ) {
		const seen = new Set();
		return Array.from( step.querySelectorAll( 'input:not([type=hidden]), select, textarea' ) ).filter( ( el ) => {
			if ( el.name === 'site_web' || seen.has( el.name ) ) {
				return false;
			}
			seen.add( el.name );
			return true;
		} );
	}

	function checkStep( index ) {
		let firstInvalid = null;
		fieldsOf( steps[ index ] ).forEach( ( field ) => {
			const message = messageFor( field );
			setError( field, message );
			if ( message && ! firstInvalid ) {
				firstInvalid = field.type === 'radio' ? form.querySelector( `[name="${ field.name }"]` ) : field;
			}
		} );
		if ( firstInvalid ) {
			firstInvalid.focus();
		}
		return ! firstInvalid;
	}

	/* ---------- Étapes ---------- */

	function show( index, focus = true ) {
		current = index;
		steps.forEach( ( step, n ) => ( step.hidden = n !== index ) );
		marks.forEach( ( mark, n ) => {
			mark.classList.toggle( 'is-done', n < index );
			if ( n === index ) {
				mark.setAttribute( 'aria-current', 'step' );
			} else {
				mark.removeAttribute( 'aria-current' );
			}
		} );
		if ( index === last ) {
			fillRecap();
		}
		if ( focus ) {
			steps[ index ].focus( { preventScroll: true } );
			form.scrollIntoView( { behavior: matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth', block: 'start' } );
		}
	}

	function next() {
		if ( checkStep( current ) && current < last ) {
			show( current + 1 );
		}
	}

	/* ---------- Champs conditionnels ---------- */

	function updateConditions() {
		form.querySelectorAll( '[data-show-if]' ).forEach( ( wrap ) => {
			const [ name, value ] = wrap.dataset.showIf.split( '=' );
			const control = form.querySelector( `[name="${ name }"]:checked, select[name="${ name }"]` );
			const visible = !! control && control.value === value;
			wrap.hidden = ! visible;
			wrap.querySelectorAll( 'input' ).forEach( ( input ) => ( input.required = visible ) );
		} );
		const commune = $( '#devis-commune' );
		const ville = $( '#devis-ville' ).value.trim();
		if ( ville.toLowerCase() === 'abidjan' ) {
			commune.setAttribute( 'list', 'devis-communes' );
			commune.placeholder = 'Par exemple Cocody, Yopougon, Port-Bouët…';
		} else {
			commune.removeAttribute( 'list' );
			commune.placeholder = ville ? 'Commune ou quartier du site' : '';
		}
	}

	/* ---------- Récapitulatif ---------- */

	function valueOf( field ) {
		if ( field.type === 'radio' ) {
			const checked = form.querySelector( `[name="${ field.name }"]:checked` );
			return checked ? checked.parentElement.textContent.trim() : '';
		}
		if ( field.tagName === 'SELECT' ) {
			return field.value ? field.selectedOptions[ 0 ].textContent.trim() : '';
		}
		if ( field.id === 'devis-budget' ) {
			return field.value.trim() ? field.value.trim() + ' FCFA' : 'Non précisé';
		}
		return field.value.trim();
	}

	function fillRecap() {
		const recap = $( '.devis__recap' );
		const list = $( 'dl', recap );
		list.replaceChildren();
		steps.slice( 0, last ).forEach( ( step ) => {
			fieldsOf( step ).forEach( ( field ) => {
				const value = valueOf( field );
				if ( ! field.dataset.recap || ! value || field.closest( '[data-show-if][hidden]' ) ) {
					return;
				}
				const dt = document.createElement( 'dt' );
				const dd = document.createElement( 'dd' );
				dt.textContent = field.dataset.recap;
				dd.textContent = value.length > 240 ? value.slice( 0, 240 ) + '…' : value;
				list.append( dt, dd );
			} );
		} );
		recap.hidden = false;
	}

	/* ---------- Pièces jointes et compteur ---------- */

	function listFiles( input ) {
		const list = $( '.devis__files' );
		list.replaceChildren(
			...Array.from( input.files || [] ).map( ( file ) => {
				const li = document.createElement( 'li' );
				li.textContent = `${ file.name } (${ mb( file.size ) })`;
				return li;
			} )
		);
		setError( input, filesError( input ) );
	}

	function updateCounter() {
		const area = $( '#devis-description' );
		const counter = $( '[data-counter="description"]' );
		counter.textContent = `${ area.value.length } / ${ area.maxLength } caractères`;
	}

	/* ---------- Mise en route ---------- */

	form.noValidate = true;
	form.classList.add( 'is-stepped' );
	form.querySelectorAll( '[data-nav]' ).forEach( ( button ) => ( button.hidden = false ) );

	form.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-nav], [data-goto]' );
		if ( ! button ) {
			return;
		}
		if ( button.dataset.goto ) {
			show( Number( button.dataset.goto ) - 1 );
		} else if ( button.dataset.nav === 'next' ) {
			next();
		} else {
			show( Math.max( 0, current - 1 ) );
		}
	} );

	form.addEventListener( 'change', ( event ) => {
		const field = event.target;
		if ( field.name === 'ville' || field.name === 'service' ) {
			updateConditions();
		}
		if ( field.type === 'file' ) {
			listFiles( field );
		} else if ( field.getAttribute( 'aria-invalid' ) === 'true' ) {
			setError( field, messageFor( field ) );
		}
	} );

	form.addEventListener( 'input', ( event ) => {
		if ( event.target.id === 'devis-description' ) {
			updateCounter();
		}
		if ( event.target.id === 'devis-ville' ) {
			updateConditions();
		}
		if ( event.target.id === 'devis-budget' ) {
			// Chiffres seuls, groupés par milliers : 5000000 -> 5 000 000.
			const digits = event.target.value.replace( /\D/g, '' ).replace( /^0+/, '' ).slice( 0, 13 );
			event.target.value = digits.replace( /\B(?=(\d{3})+(?!\d))/g, ' ' );
		}
		if ( event.target.getAttribute( 'aria-invalid' ) === 'true' && ! messageFor( event.target ) ) {
			setError( event.target, '' );
		}
	} );

	form.addEventListener( 'submit', ( event ) => {
		// Touche Entrée dans une étape intermédiaire : on passe à l'étape suivante au lieu d'envoyer.
		if ( current < last ) {
			event.preventDefault();
			next();
			return;
		}
		for ( let i = 0; i <= last; i++ ) {
			if ( ! checkStep( i ) ) {
				event.preventDefault();
				if ( i !== current ) {
					show( i, false );
					checkStep( i );
				}
				return;
			}
		}
		const submit = $( '.devis__submit' );
		submit.disabled = true;
		submit.textContent = 'Envoi en cours…';
	} );

	updateConditions();
	updateCounter();
	show( Math.min( last, Math.max( 0, Number( form.dataset.step ) - 1 ) ), false );
	const summary = $( '.devis__summary' );
	if ( summary ) {
		summary.focus();
	}
} )();
