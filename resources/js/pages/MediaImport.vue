<script setup>
  import { onMounted, watch } from 'vue'
  import axios from 'axios'
  import { saveAs } from 'file-saver'

  import {
    //Ref Vars
    dialog,
    step,
    summary,
    isValid,
    fileInput,
    previous,
    finished,
    steps,

    //Functions
    openModal,
    closeModal,
    backStep,
    nextStep,
    downloadTemplate,
    handleFileUpload,
    handleFileDrop,
    triggerFileInput,
    uploadFile,
    removeCompletedClassList
  } from '../helpers/functions.js'

  onMounted(() => {
    dialog.value = true
    setTimeout(() => removeCompletedClassList(), 200)
  })

  watch(isValid, (newIsValid) => {
    if (previous.value) 
    {
      if (step.value < 2 && newIsValid) 
      {
        nextStep()
        finished.value = false
      }
      if (step.value >= 2 && newIsValid) 
      {
        setTimeout(() => {
          nextStep()
          finished.value = true
        }, 1000)
      }
    }
  })
</script>

<template>
  <v-container>
    <div class="text-center">
      <v-btn v-if="!dialog" @click="openModal">
        Open Media
      </v-btn>
    </div>
    <!-- Modal with v-stepper -->
    <v-dialog v-model="dialog" max-width="800" persistent>
      <v-toolbar flat>
        <v-toolbar-title>Media database import</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="closeModal">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-stepper v-model="step">
        <!-- Stepper Header -->
        <v-stepper-header>
          <template v-for="(item, index) in steps" :key="index">
            <v-stepper-item 
              :complete="step > index"
              :rules="[() => !!item.valid]"
              :title="item.title" 
              :subtitle="item.subtitle"
              :value="item.value" 
              :class="{'d-none': item.value === 3}"
              >
            </v-stepper-item>
          </template>
        </v-stepper-header>
        <!-- Stepper Windows -->
        <v-stepper-window>
          <v-stepper-window-item value="0">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Download template</h3>
                <p>Insert your data into the template and upload the file to update the media database.</p>
                <v-btn @click="downloadTemplate">Download</v-btn>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
          <v-stepper-window-item value="1">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Upload database</h3>
                <div 
                  class="file-drop-area"
                  @drop.prevent="handleFileDrop"
                  @dragover.prevent>
                  <v-icon class="google-doc-icon mb-4" size="36" color="blue">mdi-file-document</v-icon>
                  <h4>Drag & drop .csv or .xls file here</h4>
                  <p>Or, click to upload <a href="#" @click.prevent="triggerFileInput">here</a>.</p>
                </div>
                <input 
                  type="file" 
                  ref="fileInput" 
                  style="display: none;" 
                  @change="handleFileUpload"
                />
                <v-list v-if="summary.errors">
                  <v-list-item v-for="(error, index) in summary.errors" :key="index">
                    <div class="error-container">
                      <ul>
                        <li v-for="msg in error.messages" :key="msg">
                          <strong color="green" v-if="error.row">Row {{ error.row }}:</strong>
                          <span class="ms-2 error-message">{{msg}}</span>
                        </li>
                      </ul>
                    </div>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
          <v-stepper-window-item value="2">
            <div class="d-flex justify-center align-center stepper-loader">
              <v-progress-circular
                color="primary"
                indeterminate></v-progress-circular>
            </div>
          </v-stepper-window-item>
          <v-stepper-window-item value="3">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Import Summary</h3>
                <p v-if="summary.media">
                  Successfully imported {{ summary.success }} of {{ summary.total }} media. Review the table below for any errors encountered during the import process.
                </p>
                <v-table>
                  <thead>
                    <tr>
                      <th><strong>Row</strong></th>
                      <th><strong>Details</strong></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in summary.media" :key="item.id">
                      <td :class="{'text-red': item.status === 'Import failed', 'text-dark': item.status === 'Imported', 'text-blue': item.status === 'Clone imported'}">
                        {{ index + 1 }}
                      </td>
                      <td :class="{'text-red': item.status === 'Import failed', 'text-dark': item.status === 'Imported', 'text-blue': item.status === 'Clone imported'}">
                        <strong>{{ item.status }}</strong>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
        </v-stepper-window>  
        <!-- Stepper Actions -->
        <v-stepper-actions>
            <template #prev>
              <v-btn 
                class="me-2 white--text btn-back"
                outlined
                text="Back"
                @click="backStep"
                :disabled="finished || isValid">
              </v-btn>
            </template>
            <template #next>
              <v-btn
                class="bg-green-darken-3"  
                color="white"
                text="Next"
                @click="nextStep"
                :disabled="isValid && step >= 1">
              </v-btn>
            </template>
        </v-stepper-actions> 
      </v-stepper>
    </v-dialog>
  </v-container>
</template>

