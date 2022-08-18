/**
 * Функция подгрузки Актуальных БЕ для пользователя с помощью ajax с возможностью поиска по подстроке названия БЕ
 *
 * @param search_key - параметр строковый для поиска по имени (нескольким символам) в списке БЕ закрепленные за пользователем
 * @param user_id - параметр цифровой должен содержать идентификатор пользователя по которым производится подгрузка БЕ
 */
function loaderBE(search_key, user_id) {

    $.ajax({
        url: '/local/ajax/user-card/search_be.php?exec=true',
        method: 'POST',
        data: {
            src: search_key,
            uid: user_id,
            notHtml: true
        },
        success: function (response) {
            $('body').find('.salary-be').html(response);
        }
    })
}