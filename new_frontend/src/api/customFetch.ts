import { HttpError, HttpServerError, makeError } from './http_error'
import { useAuthStore } from '@/stores/auth'
import { ValidationError } from './violations_error'

export async function customFetch(input: RequestInfo, init: RequestInit = {}) {
  const token = useAuthStore.getState().token

  const headers = new Headers(init.headers || {})
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  if (!headers.has('Content-Type') && init.method) {
    const mthd = init.method.toUpperCase();

    if (mthd === 'PATCH') {
      headers.set('Content-Type', 'application/merge-patch+json')
    } else if (['POST', 'PUT'].includes(mthd)) {
      headers.set('Content-Type', 'application/ld+json')
    }
  }

  try {
    const response = await fetch(input, {
      ...init,
      headers,
    })

    if (!response.ok) {
      if (response.status > 499) {
        throw new HttpServerError({
          message: `errors.5xx.title`,
          submessage: `errors.5xx.message`,
          status: response.status,
        })
      }

      if ([401, 403, 404].includes(response.status)) {
        throw makeError(response.status);
      }

      let errorBody: any = null

      try {
        console.log('Preparse');
        errorBody = await response.clone().json()
        console.log('Postparse');

        if (response.status === 422) {
          console.log('is 422');
          throw new ValidationError(errorBody.violations || []);
        }
      } catch (err: any) {
        if (err instanceof ValidationError) {
          throw err;
        }

        try {
          errorBody = await response.clone().text()
        } catch {
          // ignore
        }
      }

      throw new HttpError({
        message: `HTTP error ${response.status} on ${response.url}`,
        status: response.status,
        body: errorBody,
      })
    }

    return response
  } catch (err) {
    if (err instanceof HttpError) throw err

    throw new HttpError({
      message: `Network error on ${typeof input === 'string' ? input : input.toString()}`,
      status: 0,
    })
  }
}