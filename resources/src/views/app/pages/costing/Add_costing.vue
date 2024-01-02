<template>
  <div class="main-content">
    <breadcumb :page="$t('Costing')" :folder="$t('List')" />
    <div
      v-if="isLoading"
      class="loading_page spinner spinner-primary mr-3"
    ></div>

    <validation-observer ref="Create_Costing" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Product" enctype="multipart/form-data">
        <b-row>
          <b-col md="12" class="mb-2">
            <b-card class="mt-3">
              <b-row>
                <!-- Customer -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Customer"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Customer') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        v-model="costing.client_id"
                        @input="Selected_customer"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose_Customer')"
                        :options="
                          clients.map((clients) => ({
                            label: clients.name,
                            value: clients.id,
                          }))
                        "
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Creation Date -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Date"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Date') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Date"
                        type="date"
                        :placeholder="$t('Date')"
                        v-model="costing.order_date"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Active -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Active"
                    :rules="{ required: true, min: 3, max: 55 }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Active') + ' ' + '*'">
                      <b-form-checkbox
                        v-model="costing.active"
                        name="Active"
                        switch
                      >
                        <b>{{ costing.active }}</b>
                      </b-form-checkbox>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> </b-row
              ><b-row>
                <!-- Box No -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Box No"
                    :rules="{ required: true, min: 1, max: 60 }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Box No') + ' ' + '*'">
                      <b-form-input
                        v-b-tooltip.hover
                        title="12.0X35.0X34.2"
                        @input="conversionFormula"
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Box No"
                        :placeholder="$t('Box No')"
                        v-model="costing.box_size"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Measurement -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Measurement"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Measurement') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose Measurement')"
                        v-model="costing.measurement"
                        :options="[
                          { label: 'cm', value: 'cm' },
                          { label: 'inch', value: 'inch' },
                        ]"
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Quantity -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="Quantity"
                    :rules="{ required: true, min: 1, max: 5 }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Quantity') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Quantity"
                        type="number"
                        :placeholder="$t('Quantity')"
                        v-model="costing.quantity"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Category -->
                <b-col md="3" class="mb-2">
                  <validation-provider
                    name="category"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Box Type') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Box Category Type')"
                        v-model="costing.category_id"
                        :options="
                          categories.map((categories) => ({
                            label: categories.name,
                            value: categories.id,
                          }))
                        "
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Shade -->
                <b-col md="3" class="mb-2">
                  <validation-provider name="shade" :rules="{ required: true }">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Shade') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose Shade')"
                        v-model="costing.shade"
                        :options="
                          shades.map((shades) => ({
                            label: shades.name,
                            value: shades.id,
                          }))
                        "
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Paper Type -->
                <b-col md="3" class="mb-2">
                  <validation-provider name="type" :rules="{ required: true }">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Paper Type') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose Paper Type')"
                        v-model="costing.type"
                        :options="
                          types.map((types) => ({
                            label: types.name,
                            value: types.id,
                          }))
                        "
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- No of Ply -->
                <b-col md="3" class="mb-2">
                  <validation-provider name="Ply" :rules="{ required: true }">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Ply') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose Ply')"
                        v-model="costing.ply"
                        :options="[
                          { label: '1', value: '1' },
                          { label: '3', value: '3' },
                          { label: '5', value: '5' },
                          { label: '7', value: '7' },
                          { label: '9', value: '9' },
                        ]"
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>
                <b-col md="3" class="mb-2">
                  <b-form-group :label="$t('')"
                    ><v-btn
                      prepend-icon="$tick"
                      variant="tonal"
                      style="color: #ffff; margin-left: 10px"
                      @click="add_variant(costing.ply)"
                      class="ms-3 btn btn-md btn-primary mt-4"
                    >
                      {{ $t("Add") }}
                    </v-btn>
                  </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <!-- BOX Size -->
                <!-- LENGHT CM -->
                <b-col md="6" class="mb-2">
                  <b-row>
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Length"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Length') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Length"
                            type="number"
                            disabled
                            :placeholder="$t('0')"
                            v-model="costing.box_length_cm"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- WIDTH CM -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Width"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Width') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Width"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.box_width_cm"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- LENGHT CM -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Height"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Height') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Height"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.box_height_cm"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                  </b-row>
                  <b-row>
                    <!-- BOX SIZE -->
                    <!-- LENGHT INCH -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Length Inches"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Length') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Length"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.box_length_inch"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- WIDTH INCH -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Width Inches"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Width') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Width"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.box_width_inch"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- LENGHT INCH -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Box Height Inches"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Height') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Height"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.box_height_inch"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                  </b-row>
                </b-col>
                <b-col md="6" class="mb-2">
                  <b-row>
                    <!-- Sheet Size -->
                    <!-- SHEET LENGHT  -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Sheet Length"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Sheet Length') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Sheet Length"
                            type="number"
                            disabled
                            :placeholder="$t('0')"
                            v-model="costing.sheet_length"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- SHEET WIDTH -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Sheet Width"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Sheet Width') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Sheet Width"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.sheet_width"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col> </b-row
                  ><b-row>
                    <!-- SHEET Count -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Sheet Count"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Sheet Count') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Number of Sheets/Roll"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.sheet_count"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- Roll One Side -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Roll One Side"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Roll One Side') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Number of Roll One Side"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.roll_one_side"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <!-- Roll Two Side -->
                    <b-col md="3" class="mb-2">
                      <validation-provider
                        name="Roll Two Side"
                        :rules="{ required: true, min: 1, max: 11 }"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Roll Two Side') + ' ' + '*'">
                          <b-form-input
                            :state="getValidationState(validationContext)"
                            aria-describedby="Name-feedback"
                            label="Total Roll Two Side"
                            disabled
                            type="number"
                            :placeholder="$t('0')"
                            v-model="costing.roll_two_side"
                          ></b-form-input>
                          <b-form-invalid-feedback id="Name-feedback">{{
                            validationContext.errors[0]
                          }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                  </b-row>
                </b-col>
              </b-row>
              <b-row>
                <!-- <b-col md="12" class="mb-2">
                  <b-form-group :label="$t('Description')">
                    <textarea
                      rows="4"
                      class="form-control"
                      :placeholder="$t('Afewwords')"
                      v-model="costing.note"
                    ></textarea>
                  </b-form-group>
                </b-col> -->
              </b-row>
            </b-card>

            <b-card class="mt-3">
              <b-row>
                <div class="col-md-9 mb-3 mt-3">
                  <div class="d-flex">
                    <legend
                      tabindex="-1"
                      class="bv-no-focus-ring col-form-label pt-0"
                      id="__BVID__141__BV_label_"
                    >
                      Paper Specification
                    </legend>
                  </div>
                  <div class="d-flex"></div>
                </div>

                <div class="col-md-12 mb-2">
                  <div class="table">
                    <table class="table table-hover table-sm">
                      <thead class="bg-gray-300">
                        <tr>
                          <th scope="col">{{ $t("Ply No.") }}</th>
                          <th scope="col">{{ $t("Type of Paper") }}</th>
                          <th scope="col">{{ $t("Paper") }}</th>
                          <th scope="col">{{ $t("BF") }}</th>
                          <th scope="col">{{ $t("Rate") }}</th>
                          <th scope="col">{{ $t("Grams") }}</th>
                          <th scope="col">{{ $t("Flute Factor") }}</th>
                          <th scope="col">{{ $t("Weight") }}</th>
                          <th scope="col">{{ $t("Approx.") }}</th>
                          <th scope="col">{{ $t("Cost") }}</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="variants.length <= 0">
                          <td colspan="11">{{ $t("NodataAvailable") }}</td>
                        </tr>
                        <tr v-for="(variant, index) in variants">
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.ply_no"
                            />
                          </td>
                          <td>
                            <v-select
                              v-model="variant.layer"
                              :key="index"
                              :placeholder="$t('Choose Layer')"
                              @input="updatePapers(index)"
                              :options="[
                                { label: 'Craft', value: 'Craft' },
                                {
                                  label: 'Folding Nali',
                                  value: 'Folding Nali',
                                },
                                {
                                  label: 'Folding',
                                  value: 'Folding',
                                },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            <v-select
                              :reduce="(label) => label.value"
                              :key="index"
                              @input="updateRates(index)"
                              :placeholder="$t('Choose Paper Type')"
                              v-model="variant.paper"
                              :options="
                                papers.map((papers) => ({
                                  label: papers.name,
                                  value: papers.id,
                                }))
                              "
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.bf"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.rate"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.gram"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.flute_factor"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.weight"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.approx"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              disabled
                              v-model="variant.cost"
                            />
                          </td>
                          <td>
                            <a
                              style="color: #ffff"
                              @click="delete_variant(variant.var_id)"
                              class="btn btn-sm btn-danger"
                              title="Delete"
                            >
                              <i class="i-Close-Window"></i>
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </b-row>
            </b-card>

            <b-card class="mt-3">
              <b-row>
                <div class="offset-md-5 col-md-3 mt-4">
                  <table class="table table-striped table-sm">
                    <tbody>
                      <tr>
                        <td colspan=3 class="font-weight-bold">
                          {{ $t("Paper Weight Calculation") }}
                        </td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Craft") }}</td>
                        <td><span>{{ costing.total_craft.toFixed(2) }}</span></td>
                        <td class="font-weight-bold">{{ costing.total_craft_q.toFixed(2) }}</td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Folding Nali") }}</td>
                        <td><span>{{ costing.total_folding_nali.toFixed(2) }}</span></td>
                        <td class="font-weight-bold">{{ costing.total_folding_nali_q.toFixed(2) }}</td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Folding") }}</td>
                        <td><span>{{ costing.total_folding.toFixed(2) }}</span></td>
                        <td class="font-weight-bold">{{ costing.total_folding_q.toFixed(2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="offset-md-1 col-md-3 mt-4">
                  <table class="table table-striped table-sm">
                    <tbody>
                        <tr>
                            <td colspan=3 class="font-weight-bold">
                              {{ $t("Box Specification and Cost") }}
                            </td>
                        </tr>
                    <tr>
                        <td class="bold">{{ $t("Total Grams") }}</td>
                        <td></td>
                        <td class="text-right"><span>{{ costing.total_grams.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Total BS") }}</td>
                        <td></td>
                        <td class="text-right"><span>{{ costing.total_bs.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Total Weight") }}</td>
                        <td></td>
                        <td class="text-right"><span>{{ costing.total_weight.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Carrogation Cost") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.carrogation_cost_percent" value="5.00" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.carrogation_cost.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Waste") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.waste_percent" value="1" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.waste.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Raw Material Cost") }}</td>
                        <td></td>
                        <td class="text-right"><span>{{ costing.total_cost.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Conversion Rs/KG") }}</td>
                              <td><input required class="form-control" type="number" v-model="costing.conversion_per_kg_percent" value="10.00" size="4" @input="totalRateUpdate()" /></td>
                        <td class="text-right"><span>{{ costing.conversion_per_kg.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Printing") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.printing_percent" value="2.00" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.printing.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Lamination") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.lamination_percent" value="5.00" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.lamination.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Profit %") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.profit_percent" value="10.00" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.profit.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td class="bold">{{ $t("Transport") }}</td>
                        <td><input required class="form-control" type="number" v-model="costing.transport_percent" value="10.00" size="4" @input="totalRateUpdate()"/></td>
                        <td class="text-right"><span>{{ costing.transport.toFixed(2) }}</span></td>
                      </tr>
                      <tr>
                        <td>
                          <span class="font-weight-bold">{{
                            $t("Final Box Price")
                          }}</span>
                        </td>
                        <td></td>
                        <td class="text-right">
                          <span class="font-weight-bold">{{
                            costing.final_box_price.toFixed(2)
                          }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </b-row>
            </b-card>
          </b-col>

          <b-col md="12" class="mt-3">
            <b-button
              variant="primary"
              type="submit"
              :disabled="SubmitProcessing"
              ><i class="i-Yes me-2 font-weight-bold"></i>
              {{ $t("submit") }}</b-button
            >
            <div v-once class="typo__p" v-if="SubmitProcessing">
              <div class="spinner sm spinner-primary mt-3"></div>
            </div>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>


<script>
import VueUploadMultipleImage from "vue-upload-multiple-image";
import VueTagsInput from "@johmun/vue-tags-input";
import NProgress from "nprogress";
import axios from "axios";

export default {
  metaInfo: {
    title: "Create Product",
  },
  data() {
    return {
      size: "",
      len: 8,
      change: false,
      isLoading: true,
      SubmitProcessing: false,
      data: new FormData(),
      clients: [],
      categories: [],
      units: [],
      types: [],
      reelsize: [],
      grams: [],
      papers: [],
      shades: [],
      roles: {},
      variants: [],
      costing: {
        client_id: "",
        order_date: "",
        box_size: "",
        quantity: 1000,
        box_length_cm: 0,
        box_width_cm: 0,
        box_height_cm: 0,
        box_length_inch: 0,
        box_width_inch: 0,
        box_height_inch: 0,
        sheet_length: 0,
        sheet_width: 0,
        sheet_count: 0,
        roll_one_side: 0,
        roll_two_side: 0,
        total_grams: 0,  // Total GMS
        total_bs: 0, // Total BS
        total_weight: 0, // total_craft_q + total_folding_nali_q + total_folding_q
        total_cost: 0, // its same as raw_material_cost
        total_craft: 0,
        total_folding_nali: 0,
        total_folding: 0,
        total_craft_q: 0,
        total_folding_nali_q: 0,
        total_folding_q: 0,
        carrogation_cost: 5,
        waste: 1,
        profit: 10,
        raw_material_cost: 0, // Total Cost
        conversion_per_kg: 10,
        printing: 2,
        lamination: 5,
        transport: 2,
        carrogation_cost_percent: 5,
        waste_percent: 1,
        profit_percent: 0,
        raw_material_cost_percent: 0, // Total Cost
        conversion_per_kg_percent: 10,
        printing_percent: 2,
        lamination_percent: 5,
        transport_percent: 2,
        profit_percent: 10,
        final_box_price: 0,
        measurement: "inch",
        ply: 5,
      },
      code_exist: "",
    };
  },

  components: {
    VueUploadMultipleImage,
    VueTagsInput,
  },

  methods: {
    // Conversion Formula
    conversionFormula() {
      // Remove spaces from the input value
      const trimmedValue = (this.costing.box_size.trim()).replace(/\s/g, '');
      console.log(trimmedValue);

      // Split the input value using case-insensitive x/X
      const numbers = trimmedValue.split(/x/i);

      this.costing.box_length_cm = (numbers[0] * 2.54).toFixed(2);
      this.costing.box_width_cm = (numbers[1] * 2.54).toFixed(2);
      this.costing.box_height_cm = (numbers[2] * 2.54).toFixed(2);
      this.costing.box_length_inch = numbers[0];
      this.costing.box_width_inch = numbers[1];
      this.costing.box_height_inch = numbers[2];
      this.costing.sheet_length = (
        (parseFloat(this.costing.box_length_inch) +
          parseFloat(this.costing.box_width_inch)) *
          2 +
        2
      ).toFixed(2);
      this.costing.sheet_width = Math.ceil(
        parseFloat(this.costing.box_width_inch) +
          parseFloat(this.costing.box_height_inch)
      ).toFixed(2);
      this.costing.sheet_count = (2400 / this.costing.sheet_length).toFixed(2);
      this.costing.roll_one_side = (
        this.costing.quantity / this.costing.sheet_count
      ).toFixed(2);
      this.costing.roll_two_side = (2 * this.costing.roll_one_side).toFixed(2);
    },

    updateRates(index) {
      console.log("Index: " + index);
      console.log(this.variants[index].layer);
      console.log(this.variants[index].paper);
      var paperInd = this.papers.findIndex(
        (paper) => paper.id === this.variants[index].paper
      );
      console.log(paperInd);

      this.variants[index].gram = this.papers[paperInd].paper_grams;
      this.variants[index].rate = this.papers[paperInd].price;
      this.variants[index].bf = this.papers[paperInd].width;
      var calcWeight = parseFloat(
        (this.costing.sheet_length *
          this.costing.sheet_width *
          1 *
          this.variants[index].gram *
          this.variants[index].flute_factor) /
          1550 /
          1000
      ).toFixed(4);
      var calcWeight2 = parseFloat(
        (this.costing.sheet_length *
          this.costing.sheet_width *
          1 *
          this.variants[index].gram *
          this.variants[index].flute_factor) /
          1550 /
          1000
      ).toFixed(3);
      this.variants[index].weight = calcWeight;
      this.variants[index].approx = calcWeight2;
      this.variants[index].cost = (this.variants[index].rate * this.variants[index].approx).toFixed(2);

      console.log(this.costing.total_craft);
      if (this.variants[index].layer=='Craft'){
        this.costing.total_craft += parseFloat(calcWeight);
        this.costing.total_craft_q += parseFloat(calcWeight*this.costing.quantity);
      } else if (this.variants[index].layer=='Folding Nali'){
        this.costing.total_folding_nali += parseFloat(calcWeight);
        this.costing.total_folding_nali_q += parseFloat(calcWeight*this.costing.quantity);
      } else if (this.variants[index].layer=='Folding'){
        this.costing.total_folding += parseFloat(calcWeight);
        this.costing.total_folding_q += parseFloat(calcWeight*this.costing.quantity);
      }

      this.costing.total_grams += parseFloat(calcWeight2);
      this.costing.total_cost += parseFloat(this.variants[index].cost);
      this.totalRateUpdate();
    },

    totalRateUpdate(){
        this.costing.carrogation_cost = this.costing.carrogation_cost_percent;
        this.costing.raw_material_cost = this.costing.total_cost;
        this.costing.waste = parseFloat((this.costing.total_cost * this.costing.waste_percent)/100);
        this.costing.conversion_per_kg = parseFloat((this.costing.total_cost * this.costing.conversion_per_kg_percent)/100);
        this.costing.printing = parseFloat((this.costing.total_cost * this.costing.printing_percent)/100);
        this.costing.lamination = parseFloat((this.costing.total_cost * this.costing.lamination_percent)/100);
        this.costing.profit = parseFloat((this.costing.total_cost * this.costing.profit_percent)/100);
        this.costing.transport = parseFloat((this.costing.total_cost * this.costing.transport_percent)/100);



        this.costing.total_weight = parseFloat(this.costing.total_craft_q+this.costing.total_folding_nali_q+this.costing.total_folding_q);
        this.costing.final_box_price = parseFloat(this.costing.carrogation_cost+this.costing.waste+this.costing.raw_material_cost+this.costing.conversion_per_kg+this.costing.printing+this.costing.lamination+this.costing.profit+this.costing.transport+this.costing.total_grams);
    },

    updatePapers(index)
    {
      // this.variants.some((variant) => variant.ply_no === tag)
      console.log("Index: " + index);

      const variant2 = this.variants[index].layer;
      console.log("YES " + variant2);

      //   this.variants[index].flute_factor = 1.33;
      if (variant2 == "Folding Nali") {
        this.variants[index].flute_factor = 1.33;
      } else {
        this.variants[index].flute_factor = 1;
      }
    },

    async filterProducts() {
      const response = await axios.get("/api/products", {
        params: {
          type: this.productType,
          color: this.productColor,
        },
      });

      this.products = response.data;
    },
    //------ Generate code
    generateNumber() {
      this.code_exist = "";
      this.costing.code = Math.floor(
        Math.pow(10, 7) +
          Math.random() * (Math.pow(10, 8) - Math.pow(10, 7) - 1)
      );
    },

    //------------- Submit Validation Create Product
    Submit_Product() {
      this.$refs.Create_Costing.validate().then((success) => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (this.costing.type == "is_variant" && this.variants.length <= 0) {
            this.makeToast(
              "danger",
              "The Paper Specification array is required.",
              this.$t("Failed")
            );
          } else {
            this.Create_Costing();
          }
        }
      });
    },

    add_variant(tag) {
      if (
        this.variants.length > 0 &&
        this.variants.some((variant) => variant.ply_no === tag)
      ) {
        this.makeToast(
          "warning",
          this.$t("VariantDuplicate"),
          this.$t("Warning")
        );
      } else {
        if (this.tag != "") {
          if (tag == 3) {
            var typeLayer = ["Craft", "Folding Nali", "Craft"];
            var factor = [1, 1.33, 1];
          } else if (tag == 5) {
            var typeLayer = [
              "Craft",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Craft",
            ];
            var factor = [1, 1.33, 1, 1.33, 1];
          } else if (tag == 7) {
            var typeLayer = [
              "Craft",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Craft",
            ];
            var factor = [1, 1.33, 1, 1.33, 1, 1.33, 1];
          } else if (tag == 9) {
            var typeLayer = [
              "Craft",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Folding",
              "Folding Nali",
              "Craft",
            ];
            var factor = [1, 1.33, 1, 1.33, 1, 1.33, 1, 1.33, 1];
          } else {
            var typeLayer = ["Craft"];
            var factor = [1];
          }
          console.log(tag);
          for (var i = 0; i < tag; i++) {
            console.log(i);
            var variant_tag = {
              var_id: this.variants.length + i, // generate unique ID
              ply_no: i + 1,
              layer: typeLayer[i],
              flute_factor: factor[i],
              gram: 0,
              bf: 0,
              rate: 0,
              weight: 0,
              approx: 0,
              cost: 0,
            };
            this.variants.push(variant_tag);
            this.ply_no = "";
          }
        } else {
          this.makeToast(
            "warning",
            "Please enter the size",
            this.$t("Warning")
          );
        }
      }
    },

    //-----------------------------------Delete variant------------------------------\\
    delete_variant(var_id) {
      for (var i = 0; i < this.variants.length; i++) {
        if (var_id === this.variants[i].var_id) {
          this.variants.splice(i, 1);
        }
      }
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true,
      });
    },

    //------ Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Event upload Image Success
    uploadImageSuccess(formData, index, fileList, imageArray) {
      this.images = fileList;
    },

    //------ Event before Remove Image
    beforeRemove(index, done, fileList) {
      var remove = confirm("remove image");
      if (remove == true) {
        this.images = fileList;
        done();
      } else {
      }
    },

    //-------------- Product Get Elements
    GetElements() {
      axios
        .get("costing/create")
        .then((response) => {
          this.clients = response.data.clients;
          this.categories = response.data.categories;
          this.types = response.data.types;
          this.units = response.data.units;
          this.reelsize = response.data.reelsize;
          this.grams = response.data.grams;
          this.papers = response.data.papers;
          this.shades = response.data.shades;
          this.isLoading = false;
        })
        .catch((response) => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------- Get Sub Units with Unit id ------------------------------\\
    Get_Units_SubBase(value) {
      axios
        .get("get_sub_units_by_base?id=" + value)
        .then(({ data }) => (this.units_sub = data));
    },

    //---------------------- Event Select Unit Product ------------------------------\\
    Selected_Unit(value) {
      this.units_sub = [];
      this.costing.unit_sale_id = "";
      this.costing.unit_purchase_id = "";
      this.Get_Units_SubBase(value);
    },

    //------------------------------ Create new Product ------------------------------\\
    Create_Costing() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.SubmitProcessing = true;

      //   // append objet product
      if (self.costing) {
        Object.entries(self.costing).forEach(([key, value]) => {
          self.data.append(key, value);
        });
      } else {
        console.error("self.PO is undefined or null");
      }
      // append array variants
      if (self.variants.length) {
        self.data.append("variants", JSON.stringify(self.variants));
      }

      // Send Data with axios
      axios
        .post("costing", self.data)
        .then((response) => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          self.SubmitProcessing = false;
          this.$router.push({ name: "index_purchase_order" });
          this.makeToast(
            "success",
            this.$t("Successfully_Created"),
            this.$t("Success")
          );
        })
        .catch((error) => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          self.SubmitProcessing = false;
          if (error.errors.code && error.errors.code.length > 0) {
            self.code_exist = error.errors.code[0];
            this.makeToast("danger", error.errors.code[0], this.$t("Failed"));
          } else if (
            error.errors.variants &&
            error.errors.variants.length > 0
          ) {
            this.makeToast(
              "danger",
              error.errors.variants[0],
              this.$t("Failed")
            );
          } else {
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          }
        });
    },
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function () {
    this.GetElements();
  },
};
</script>
