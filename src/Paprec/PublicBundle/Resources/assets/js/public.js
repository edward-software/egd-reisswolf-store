$(function () {

    /****************************************
     * CATALOG
     ***************************************/

    $('.addProductToQuoteButton').on('click', function () {

        $('.addProductToQuoteButton').prop("disabled", true); // On désactive tous les boutons
        const productId = $(this).data('product');
        const url = $(this).data('url');
        const qtty = $('#quantityProductSelect_' + productId).val();

        $.ajax({
            url: url,
            type: "POST",
            data: {
                "productId": productId,
                "quantity": qtty
            },
            success: function (response) {
                // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                var htmlToDisplay = response.trim();
                $("#devis-recap-item-" + productId).remove();
                $("#devis-recap").append(htmlToDisplay);
            },
            complete: function () {
                $('.addProductToQuoteButton').prop("disabled", false);
            }
        });
    });


    $('#frequencyButton2').on('click', function () {
        $('.catalog-frequency-select').prop("disabled", false);
    });

    $('#frequencyButton1').on('click', function () {
        $('.catalog-frequency-select').prop("disabled", true);
    });

    $('#catalog_next_step_button').on('click', function () {
        $('.catalog_next_step_button').prop("disabled", true);

        const url = $(this).data('url');
        const ajaxUrl = $(this).data('ajax');

        var frequencyVal = $("input:radio[name ='groupOfDefaultRadios']:checked").val();
        var data = {frequency: frequencyVal};
        if (frequencyVal === 'regular') {
            data = {
                frequency: frequencyVal,
                frequency_times: $('#catalog_frequency_times_select').val(),
                frequency_interval: $('#catalog_frequency_interval_select').val()
            }
        }

        $.ajax({
            url: ajaxUrl,
            type: "POST",
            data: data,
            success: function (response) {
                $(location).attr('href', url);
            },
            error: function (response) {
            },
            complete: function () {
                $('.catalog_next_step_button').prop("disabled", true);
            }
        });
    });

    /**
     * Ajout un seul produit au clic sur le +
     */
    $('.addOneToCartButton').click(function () {
        var url = $(this).data('url');

        var productId = (this.id).replace('addOneToCartButton', '');
        $.ajax({
            type: "POST",
            url: url,
            success: function (response) {
                // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                var htmlToDisplay = response.trim();
                $("#devis-recap-item-" + productId).remove();
                $("#devis-recap").append(htmlToDisplay);
                // On met à jour la valeur du <select> de qtty du produit
                $('#quantityProductSelect_' + productId).val(+($('#quantityProductSelect_' + productId).val()) + 1);
            }
        })
    });

    /**
     * Enlève un seul produit au clic sur le -
     */
    $('.removeOneToCartButton').click(function () {
        var url = $(this).data('url');

        var productId = (this.id).replace('removeOneToCartButton', '');
        $.ajax({
            type: "POST",
            url: url,
            success: function (response) {
                $("#devis-recap-item-" + productId).remove();
                if (JSON.stringify(response) !== '{}') {
                    // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                    var htmlToDisplay = response.trim();
                    $("#devis-recap").append(htmlToDisplay);
                }
                // On met à jour la valeur du <select> de qtty du produit
                $('#quantityProductSelect_' + productId).val(+($('#quantityProductSelect_' + productId).val()) - 1);
            }
        })
    });


    /****************************************
     * CONTACT FORM
     ***************************************/

    $('input[name*=isMultisite]').change(function () {
        if (this.value == 1) {
            $('.address-field').prop("disabled", true);
        } else if (this.value == 0) {
            $('.address-field').prop("disabled", false);
        }
    });

    $('#contact_staff_select').change(function () {
        $('.contact_staff_input').val(this.value);
    });

    $('#contact_access_select').change(function () {
        $('.contact_access_input').val(this.value);
    })
});





