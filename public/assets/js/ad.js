// au click sur le bouton
$('#add-image').click(() => {
    // je recupere le nombre delement dans le div fom-group
    const index = +$('#widget-counter').val();

    // recuperation du prototype
    // remplacement de la chaine de charactere le __name__ par l'index
    const tmpl = $('#ad_images').data('prototype').replace(/__name__/g, index);

    // ajouter le code dans la div
    $('#ad_images').append(tmpl);

    // gestion d'un bug qui supprimer deux ligne en méme temps
    $('#widget-counter').val(index + 1);

    // je gére le bouton de suppresion
    handleDeleteButtons();
});

// function supprimer une ligne
function handleDeleteButtons() {
    $('button[data-action="delete"]').click(function() {
        const target = this.dataset.target;
        $(target).remove();
    });
}

// je gére le bouton de suppresion
handleDeleteButtons();


/**
 *   permet de metre a jour le compteur de sous formulaire (sur la page d'edition)
 */
function updateCounter() {
    const count = +$('#ad_images div.form-group').length;

    $('#widget-counter').val(count);
}

updateCounter();