import { useEffect, useState } from 'react';

/**
 * Returns a value that lags behind the input by `delay` ms. Used to
 * debounce search inputs so we don't fire a new query on every keystroke.
 */
export function useDebouncedValue<T>(value: T, delay = 300): T {
  const [debounced, setDebounced] = useState(value);

  useEffect(() => {
    const id = window.setTimeout(() => setDebounced(value), delay);
    return () => window.clearTimeout(id);
  }, [value, delay]);

  return debounced;
}
