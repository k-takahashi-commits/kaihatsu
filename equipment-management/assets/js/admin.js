( function () {
	'use strict';

	function setupRepairForm() {
		var locationSelect = document.getElementById( 'repair-trouble-location-id' );
		var equipmentSelect = document.getElementById( 'repair-equipment-id' );

		if ( ! locationSelect || ! equipmentSelect ) {
			return;
		}

		var locationRow = locationSelect.closest( 'tr' );
		var equipmentRow = equipmentSelect.closest( 'tr' );
		var selectedEquipmentOption = equipmentSelect.options[ equipmentSelect.selectedIndex ];
		var selectedEquipmentLocationId = selectedEquipmentOption ? selectedEquipmentOption.getAttribute( 'data-location-id' ) : '';

		if ( locationRow && equipmentRow && equipmentRow.previousElementSibling !== locationRow ) {
			equipmentRow.parentNode.insertBefore( locationRow, equipmentRow );
		}

		if ( selectedEquipmentLocationId && ( ! locationSelect.value || locationSelect.value === '0' ) ) {
			locationSelect.value = selectedEquipmentLocationId;
		}

		filterRepairEquipmentOptions();
		locationSelect.addEventListener( 'change', filterRepairEquipmentOptions );
	}

	function filterRepairEquipmentOptions() {
		var locationSelect = document.getElementById( 'repair-trouble-location-id' );
		var equipmentSelect = document.getElementById( 'repair-equipment-id' );

		if ( ! locationSelect || ! equipmentSelect ) {
			return;
		}

		var selectedLocationId = locationSelect.value;
		var selectedOption = equipmentSelect.options[ equipmentSelect.selectedIndex ];
		var selectedOptionVisible = true;

		Array.prototype.forEach.call( equipmentSelect.options, function ( option ) {
			var optionLocationId = option.getAttribute( 'data-location-id' );
			var isPlaceholder = ! optionLocationId;
			var isVisible = isPlaceholder || ! selectedLocationId || selectedLocationId === '0' || optionLocationId === selectedLocationId;

			option.hidden = ! isVisible;
			option.disabled = ! isVisible;

			if ( option === selectedOption ) {
				selectedOptionVisible = isVisible;
			}
		} );

		if ( ! selectedOptionVisible ) {
			equipmentSelect.value = '0';
		}
	}

	document.addEventListener( 'DOMContentLoaded', setupRepairForm );
}() );
