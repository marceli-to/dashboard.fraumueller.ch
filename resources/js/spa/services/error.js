let globalRouter = null

export function registerApiErrorRouter(routerInstance) {
  globalRouter = routerInstance
}

export function handleApiError(error, fallbackMessage = 'Ein unbekannter Fehler ist aufgetreten.') {
  const status = error?.response?.status;

  if (globalRouter) {
    switch (status) {
      case 401:
        globalRouter.push('/error/401')
        return
      case 403:
        globalRouter.push('/error/403')
        return
      case 404:
        globalRouter.push('/error/404')
        return
      case 419:
        globalRouter.push('/error/419')
        return
    }
  }

  switch (status) {
    case 400:
      return 'Ungültige Anfrage.'
    case 409:
      return 'Konflikt. Möglicherweise existiert der Eintrag bereits.'
    case 500:
      return 'Ein unbekannter Fehler ist aufgetreten.'
    case 503:
      return 'Service derzeit nicht verfügbar.'
    default:
      return fallbackMessage
  }
  
}
