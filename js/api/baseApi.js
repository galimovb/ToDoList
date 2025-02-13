export async function sendRequest(method, url, data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
        },
    };

    // Если есть тело запроса, добавляем его
    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);

        if (!response.ok) {
            throw new Error(`Ошибка: ${response.statusText}`);
        }

        // Возвращаем сам объект response
        return response;
    } catch (error) {
        console.error('Ошибка при отправке запроса:', error);
        throw error;
    }
}
