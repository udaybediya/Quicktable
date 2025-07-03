package com.example.quicktable;

import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class BtnActivity extends AppCompatActivity {

    private static final String SUGGESTION_URL = "http://192.168.201.3/QuicktableAPI/fetch_suggestion.php";
    private static final String SAVE_URL = "http://192.168.201.3/QuicktableAPI/post_order.php";
    private static final String FETCH_TOKEN_URL = "http://192.168.201.3/QuicktableAPI/fetch_token.php";
    private List<String> itemNames = new ArrayList<>();
    private String tokenNumber;
    private String tableName; // To store the table name

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_btn);

        // Get the table name passed from NewtableActivity
        tableName = getIntent().getStringExtra("TABLE_NAME");

        // Ensure the table name is in the correct format (e.g., "Table-1")
        if (tableName != null && tableName.startsWith("Table-")) {
            tableName = tableName.replace("Table-", ""); // Remove the redundant "Table-" prefix
            tableName = "Table-" + tableName; // Add it back to ensure consistency
        }

        // Display the table number in the tableTextView
        TextView tableTextView = findViewById(R.id.tableTextView);
        String tableNumber = tableName.replace("Table-", ""); // Extract the number (e.g., "1")
        tableTextView.setText("Table: " + tableNumber); // Display only the number

        Button addItemBtn = findViewById(R.id.addItemBtn);
        Button saveButton = findViewById(R.id.saveButton);
        TextView tokenTextView = findViewById(R.id.tokenTextView);

        View initialContainer = findViewById(R.id.mainContainer);
        setupItemContainer(initialContainer);

        fetchItemSuggestions();
        fetchTokenNumber(tokenTextView);

        addItemBtn.setOnClickListener(v -> {
            if (areAllContainersFilled()) {
                addNewItemContainer();
            } else {
                Toast.makeText(this, "Please fill in all existing fields before adding a new item", Toast.LENGTH_SHORT).show();
            }
        });

        saveButton.setOnClickListener(v -> saveOrder());
    }

    private void fetchTokenNumber(TextView tokenTextView) {
        JsonObjectRequest request = new JsonObjectRequest(Request.Method.GET, FETCH_TOKEN_URL, null,
                response -> {
                    try {
                        tokenNumber = response.getString("next_token"); // Should now be "TK-1"
                        tokenTextView.setText("Token Number: " + tokenNumber);
                    } catch (JSONException e) {
                        Log.e("FetchToken", "JSON Exception: " + e.getMessage());
                        Toast.makeText(BtnActivity.this, "Error parsing token number", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> {
                    Log.e("FetchToken", "Volley Error: " + error.toString());
                    Toast.makeText(BtnActivity.this, "Failed to fetch token number", Toast.LENGTH_SHORT).show();
                });

        Volley.newRequestQueue(this).add(request);
    }

    private void setupItemContainer(View itemContainer) {
        AutoCompleteTextView autoComplete = itemContainer.findViewById(R.id.autoCompleteTextView);
        EditText etNumber = itemContainer.findViewById(R.id.etNumber);
        ImageButton btnDecrease = itemContainer.findViewById(R.id.btnDecrease);
        ImageButton btnIncrease = itemContainer.findViewById(R.id.btnIncrease);

        etNumber.setText("0");
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, itemNames);
        autoComplete.setAdapter(adapter);
        autoComplete.setThreshold(1);
        autoComplete.setOnItemClickListener((parent, view, position, id) -> {
            String selectedItem = (String) parent.getItemAtPosition(position);
            autoComplete.setText(selectedItem); // Ensure the selected item is set
        });

        btnIncrease.setOnClickListener(v -> {
            int current = Integer.parseInt(etNumber.getText().toString());
            etNumber.setText(String.valueOf(current + 1));
        });

        btnDecrease.setOnClickListener(v -> {
            int current = Integer.parseInt(etNumber.getText().toString());
            if (current > 0) {
                etNumber.setText(String.valueOf(current - 1));
            } else {
                Toast.makeText(this, "Value cannot be less than 0", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private boolean areAllContainersFilled() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        for (int i = 0; i < containerLayout.getChildCount(); i++) {
            View child = containerLayout.getChildAt(i);
            AutoCompleteTextView actv = child.findViewById(R.id.autoCompleteTextView);
            EditText et = child.findViewById(R.id.etNumber);

            String item = actv.getText().toString();
            String qty = et.getText().toString();

            if (item.isEmpty() || qty.equals("0")) {
                return false;
            }
        }
        return true;
    }

    private void addNewItemContainer() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        View newItem = getLayoutInflater().inflate(R.layout.item_container, containerLayout, false);

        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(10, 60, 0, 0);
        newItem.setLayoutParams(params);

        setupItemContainer(newItem);
        containerLayout.addView(newItem);
    }

    private void fetchItemSuggestions() {
        Log.d("FetchItems", "Fetching item suggestions...");
        JsonArrayRequest request = new JsonArrayRequest(Request.Method.GET, SUGGESTION_URL, null,
                response -> {
                    try {
                        itemNames.clear();
                        for (int i = 0; i < response.length(); i++) {
                            itemNames.add(response.getString(i));
                        }
                        Log.d("FetchItems", "Items fetched: " + itemNames.toString());
                        updateAllAdapters();
                    } catch (JSONException e) {
                        Log.e("FetchItems", "JSON Exception: " + e.getMessage());
                        Toast.makeText(BtnActivity.this, "Error parsing items", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> {
                    Log.e("FetchItems", "Volley Error: " + error.toString());
                    Toast.makeText(BtnActivity.this, "Failed to fetch items", Toast.LENGTH_SHORT).show();
                });

        Volley.newRequestQueue(this).add(request);
    }

    private void updateAllAdapters() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        for (int i = 0; i < containerLayout.getChildCount(); i++) {
            View child = containerLayout.getChildAt(i);
            AutoCompleteTextView actv = child.findViewById(R.id.autoCompleteTextView);
            actv.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, itemNames));
        }
    }

    private void saveOrder() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        JSONArray ordersArray = new JSONArray();

        for (int i = 0; i < containerLayout.getChildCount(); i++) {
            View child = containerLayout.getChildAt(i);
            AutoCompleteTextView actv = child.findViewById(R.id.autoCompleteTextView);
            EditText et = child.findViewById(R.id.etNumber);

            String item = actv.getText().toString().trim();  // Trim any whitespace
            String qty = et.getText().toString().trim();

            // Debugging: Log the item name and quantity
            Log.d("SaveOrder", "Item: " + item + ", Quantity: " + qty);

            // Check if the AutoCompleteTextView is empty
            if (item.isEmpty() || qty.equals("0")) {
                Log.e("SaveOrder", "Error: Empty item name or zero quantity");
                continue;
            }

            JSONObject orderObject = new JSONObject();
            try {
                orderObject.put("item_name", item);
                orderObject.put("quantity", qty);
                orderObject.put("token_number", tokenNumber);
                ordersArray.put(orderObject);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }

        if (ordersArray.length() > 0) {
            sendToServer(ordersArray);
        } else {
            Toast.makeText(this, "No valid items to save", Toast.LENGTH_SHORT).show();
        }
    }

    private void sendToServer(JSONArray ordersArray) {
        JSONObject requestBody = new JSONObject();
        try {
            requestBody.put("orders", ordersArray);
            requestBody.put("table_no", tableName); // Include table name in the request
            Log.d("RequestBody", "Request payload: " + requestBody.toString()); // Log the payload
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(Request.Method.POST, SAVE_URL, requestBody,
                response -> {
                    Log.d("ServerResponse", "Response: " + response.toString());
                    Toast.makeText(this, "Orders saved successfully!", Toast.LENGTH_SHORT).show();

                    // Now update the table status to 'occupied'
                    updateTableStatus();
                },
                error -> {
                    Log.e("ServerError", "Error: " + error.toString());
                    Toast.makeText(this, "Failed to save orders", Toast.LENGTH_SHORT).show();
                });

        Volley.newRequestQueue(this).add(request);
    }

    // Method to Update Table Status
    private void updateTableStatus() {
        String UPDATE_STATUS_URL = "http://192.168.201.3/QuicktableAPI/post_tablestatus.php";

        JSONObject statusUpdateRequest = new JSONObject();
        try {
            statusUpdateRequest.put("table_no", tableName);
            statusUpdateRequest.put("status", "occupied"); // Change status to "occupied"
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(Request.Method.POST, UPDATE_STATUS_URL, statusUpdateRequest,
                response -> {
                    Log.d("UpdateTableStatus", "Table status updated successfully");
                    finish(); // Close activity after update
                },
                error -> {
                    Log.e("UpdateTableStatus", "Error: " + error.toString());
                    Toast.makeText(this, "Failed to update table status", Toast.LENGTH_SHORT).show();
                });

        Volley.newRequestQueue(this).add(request);
    }
}