export function enforceMinMax(element) {
  if (element.value != '') {
    if (parseFloat(element.value) < parseFloat(element.min)) {
      element.value = element.min
    }
    if (parseFloat(element.value) > parseFloat(element.max)) {
      element.value = element.max
    }
  }
  // if (!'0123456789.'.includes(key)) {
  //   element.value = parseFloat(element.value)
  // }
  // console.log(key)
}

export function blockNonNumericInput(event) {
  if (event.key !== '.' && isNaN(Number(event.key))) {
    event.preventDefault()
  }
}
