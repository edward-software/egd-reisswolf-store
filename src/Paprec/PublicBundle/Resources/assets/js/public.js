$(function () {

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
    })

});





