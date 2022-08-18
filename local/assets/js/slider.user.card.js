/**
 * Инициализация JQuery при готовности страницы
 * добавлен обработчик срабатываемый на нажатие кнопки "Редактировать" в карточке пользователя и открытие слайдера
 *
 * Входных параметров нет, обязательный параметр указан в метатеге кнопки отвечающая за открытие слайдера
 * параметр data-id - содержит идентификатор пользователя открываемой карточки
 */
$(document).ready(function () {
    $('body').on('click', '#immo_id_button_edit_profile_ks_be', function () {
        var user_id = $(this).attr('data-id');
        window.BX.SidePanel.Instance.open(
            "/local/page/user_be.edit.php?UID=" + user_id,
            {
                width: 770,
                allowChangeHistory: false
            });
    });
});

/**
 * Функция инъекции кнопки "Редактировать" в карточку пользователя с обяхательным параметром
 *
 * @param user_id - обязательный параметр несущий идентификатор пользователя для правильной работы слайдера
 */
function inject(user_id) {
    $('body').find('.ui-entity-card-content-actions-block:nth-child(1)')
        .append('<span class="ui-entity-editor-content-create-lnk" id="immo_id_button_edit_profile_ks_be" data-id="'
            + user_id + '">'
            + 'Редактировать</span>');
}