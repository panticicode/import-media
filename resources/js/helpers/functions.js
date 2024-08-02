import axios from 'axios'
import { ref, computed } from 'vue'

export const dialog = ref(false)
export const step = ref(0)
export const file = ref(null)
export const summary = ref([])
export const isValid = ref(false)
export const fileInput = ref(null)
export const previous = ref(true)
export const finished = ref(true)

const items = [
    { title: 'Step 1', value: 0, subtitle: 'Download template', valid: true },
    { title: 'Step 2', value: 1, subtitle: 'Upload database', valid: true },
    { title: 'Step 3', value: 2, subtitle: 'Import summary', valid: true },
    { value: 3 }
  ]

export const steps = ref(items)

const mimeTypes = ['text/csv', 'application/vnd.ms-excel']

export const openModal = () => {
	dialog.value = true // Opens the modal
	removeCompletedClassList()
}

export const closeModal = () => {
	dialog.value = false // Closes the modal
	step.value = 0
	isValid.value = false
	steps.value.forEach(item => {
	  if (!item.valid) {
	    item.valid = true
	  }
	})
}

export const backStep = () => {
	step.value--
	isValid.value = true
	previous.value = false
	removeCompletedClassList(step.value + 1)
}

export const nextStep = () => {
	if (validate()) {
	  step.value++
	}
	isValid.value = false

	if (step.value >= 2) {
	  isValid.value = true
	}
	previous.value = true
	}

	const validate = () => {
	removeCompletedClassList(step.value + 1)
	return steps.value[step.value] && (steps.value[step.value].valid = isValid.value)
}

export const downloadTemplate = async () => {
	const templateUrl = `${location.origin}/templates/media.csv`

	try {
	  const response = await axios.get(templateUrl, { responseType: 'blob' })
	  if (response.status === 200 && mimeTypes.includes(response.data.type)) {
	    saveAs(response.data, 'template.csv')
	    isValid.value = true
	  } else {
	    console.error('File does not exist or unsupported MIME type.')
	  }
	} catch (error) {
	  console.error('Error downloading the template:', error)
	}
}

export const handleFileUpload = async (event) => {
	const files = event.target.files
	if (files.length > 0 && mimeTypes.includes(files[0].type)) {
	  file.value = files[0]
	  await uploadFile()
	}
}

export const handleFileDrop = async (event) => {
	const droppedFiles = event.dataTransfer.files
	if (droppedFiles.length > 0 && mimeTypes.includes(droppedFiles[0].type)) {
	  file.value = droppedFiles[0]
	  await uploadFile()
	}
}

export const triggerFileInput = () => {
	fileInput.value.click()
}

export const uploadFile = async () => {
	if (!file.value) return

	const formData = new FormData()
	formData.append('file', file.value)

	try {
	  const response = await axios.post('/import', formData, {
	    headers: { 'Content-Type': 'multipart/form-data' }
	  })

	  if (response.status === 200) {
	    window.Echo.channel('import-media').listen('ImportMedia', async (e) => {
	      summary.value = e.summary
	      isValid.value = !summary.value.errors
	    })
	  }
	} catch (error) {
	  console.error('Error uploading file:', error.response?.data || error.message)
	}
}

export const removeCompletedClassList = (key = null) => {
	setTimeout(() => {
	  const steppers = document.querySelectorAll('.v-stepper-item')
	  if (key !== null) {
	    steppers[key]?.classList.remove('v-stepper-item--complete')
	  } else {
	    steppers.forEach(item => item.classList.remove('v-stepper-item--complete'))
	  }
	}, 0)
}