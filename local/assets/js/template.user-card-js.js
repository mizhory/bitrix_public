/**
 * Метод инициализации JQuery при готовности документа
 */
$(document).ready(function () {
    /**
     * Событие отвечающее за нажатие кнопки закрыть слайдер
     */
    $('body').on('click', '.closeclick', function () {
        if (confirm('Вы действительно хотите закрыть окно?')) {
            window.BX.SidePanel.Instance.close()
        }
    })
    /**
     * Событие отвечающее за нажатие кнопки сохранить на слайдере
     */
    $('body').on('click', '.saveclick', function () {
        $('form[name="form-edit-usercard-be"]').submit();
    });
    /**
     * Событие отвечающее за отправку формы, логика проста - метод останавливает действие Submit
     * выбирает все данные из формы помеченные параметром name="..." и определяет их в массив
     * формируется ajax-post запрос до обработчика который передает данные формы
     * Идентификатор пользователя установлен в скрытом поле формы
     */
    $('form[name="form-edit-usercard-be"]').on('submit', function(event){
        var $ajax_data = $(this).serializeArray();

        $.ajax({
            url: '/local/ajax/user-card/ajax.php?exec=true',
            method: 'POST',
            data: $ajax_data,
            success: function (response) {
                if (
                    (typeof response != "undefined")
                    && (response == 'ok')
                ) {
                    alert('Данные успешно обновлены');
                    window.BX.SidePanel.Instance.close();
                } else {
                    //0x0A0A22 error
                    alert('Произошла не предвиденная ошибка');
                    console.error('Произошла не предвиденная ошибка при сохранении данных. Обратитесь к админстратору! Код ошибки 0x0A0A22; ' + response);
                }
            }
        });
        event.preventDefault();
    })
})