package com.example.quicktable;

import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class BillBtnActivity extends AppCompatActivity {
    private TextView tableTextView, tokenTextView, grandTotalTextView;
    private LinearLayout orderItemsLayout;
    private String currentTokenNumber;
    private Button saveButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_bill_btn);

        tableTextView = findViewById(R.id.tableTextView);
        tokenTextView = findViewById(R.id.tokenTextView);
        orderItemsLayout = findViewById(R.id.orderItemsLayout);
        grandTotalTextView = findViewById(R.id.grandTotalTextView);
        saveButton = findViewById(R.id.saveButton);

        String tableName = getIntent().getStringExtra("TABLE_NAME");
        if (tableName != null && tableName.startsWith("Table-")) {
            String tableNumber = tableName.replace("Table-", "");
            tableTextView.setText("Table name : " + tableNumber);
            new FetchTokenTask().execute(tableNumber);
        }

        saveButton.setOnClickListener(view -> updateTableStatus());
    }

    private class FetchTokenTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... params) {
            String tableNumber = params[0];
            try {
                URL url = new URL("http://192.168.201.3/QuicktableAPI/fetch_billtoken.php?table_no=Table-" + tableNumber);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String inputLine;
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();
                return response.toString();
            } catch (Exception e) {
                e.printStackTrace();
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                JSONObject jsonResponse = new JSONObject(result);
                if (jsonResponse.has("token")) {
                    currentTokenNumber = jsonResponse.getString("token");
                    tokenTextView.setText("Token Number : " + currentTokenNumber);
                    new FetchOrderDetailsTask().execute(getIntent().getStringExtra("TABLE_NAME"), currentTokenNumber);
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }

    private class FetchOrderDetailsTask extends AsyncTask<String, Void, String> {
        @Override
        protected String doInBackground(String... params) {
            String tableNo = params[0];
            String tokenNumber = params[1];
            try {
                URL url = new URL("http://192.168.201.3/QuicktableAPI/fetch_bill.php?table_no=" + tableNo + "&token_number=" + tokenNumber);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder result = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    result.append(line);
                }
                reader.close();
                return result.toString();
            } catch (Exception e) {
                e.printStackTrace();
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                JSONObject response = new JSONObject(result);
                if (response.has("items")) {
                    orderItemsLayout.removeAllViews();
                    JSONArray items = response.getJSONArray("items");
                    for (int i = 0; i < items.length(); i++) {
                        JSONObject item = items.getJSONObject(i);
                        addOrderItemRow(item.getString("item_name"), item.getString("quantity"), item.getString("item_price"), item.getString("total_price"));
                    }
                    grandTotalTextView.setText(response.getString("grand_total"));
                }
            } catch (Exception e) {
                e.printStackTrace();
                Toast.makeText(BillBtnActivity.this, "Error loading order details", Toast.LENGTH_SHORT).show();
            }
        }
    }

    private void addOrderItemRow(String itemName, String qty, String price, String total) {
        View itemView = LayoutInflater.from(this).inflate(R.layout.order_item_row, orderItemsLayout, false);
        ((TextView) itemView.findViewById(R.id.itemName)).setText(itemName);
        ((TextView) itemView.findViewById(R.id.itemQty)).setText(qty);
        ((TextView) itemView.findViewById(R.id.itemPrice)).setText(price);
        ((TextView) itemView.findViewById(R.id.itemTotal)).setText(total);
        orderItemsLayout.addView(itemView);
    }

    private void updateTableStatus() {
        String tableName = getIntent().getStringExtra("TABLE_NAME"); // e.g., "Table-1"
        String tokenNumber = currentTokenNumber;

        // 1. Update table status to "unoccupied"
        String UPDATE_STATUS_URL = "http://192.168.201.3/QuicktableAPI/post_tablestatus.php";
        JSONObject statusUpdate = new JSONObject();
        try {
            statusUpdate.put("table_no", tableName);
            statusUpdate.put("status", "unoccupied");
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest tableRequest = new JsonObjectRequest(
                Request.Method.POST, UPDATE_STATUS_URL, statusUpdate,
                response -> {
                    // 2. Update bill to "Paid"
                    String UPDATE_BILL_URL = "http://192.168.201.3/QuicktableAPI/update_bill_status.php";
                    JSONObject billUpdate = new JSONObject();
                    try {
                        billUpdate.put("table_no", tableName);
                        billUpdate.put("token_number", tokenNumber);
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }

                    JsonObjectRequest billRequest = new JsonObjectRequest(
                            Request.Method.POST, UPDATE_BILL_URL, billUpdate,
                            billResponse -> {
                                Toast.makeText(BillBtnActivity.this, "Table and bill updated!", Toast.LENGTH_SHORT).show();
                                finish();
                            },
                            error -> Toast.makeText(BillBtnActivity.this, "Failed to update bill", Toast.LENGTH_SHORT).show()
                    );
                    Volley.newRequestQueue(BillBtnActivity.this).add(billRequest);
                },
                error -> Toast.makeText(BillBtnActivity.this, "Failed to update table", Toast.LENGTH_SHORT).show()
        );
        Volley.newRequestQueue(this).add(tableRequest);
    }
}