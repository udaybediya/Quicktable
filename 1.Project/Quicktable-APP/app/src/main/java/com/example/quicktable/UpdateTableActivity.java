package com.example.quicktable;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.widget.Button;
import android.widget.GridLayout;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class UpdateTableActivity extends AppCompatActivity {

    private GridLayout gridLayout;
    private HashMap<String, Button> buttonMap;
    private Handler handler = new Handler();
    private Runnable runnable;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_update_table);

        gridLayout = findViewById(R.id.gridLayout);
        buttonMap = new HashMap<>();

        initializeButtons();
        startTableStatusUpdates();
    }

    private void initializeButtons() {
        for (int i = 1; i <= 10; i++) {
            int buttonId = getResources().getIdentifier("button" + i, "id", getPackageName());
            Button button = findViewById(buttonId);
            buttonMap.put("Table-" + i, button);
        }
    }

    private void startTableStatusUpdates() {
        runnable = new Runnable() {
            @Override
            public void run() {
                new FetchTableStatusTask().execute();
                handler.postDelayed(this, 5000);
            }
        };
        handler.post(runnable);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        handler.removeCallbacks(runnable);
    }

    private class FetchTableStatusTask extends AsyncTask<Void, Void, String> {
        @Override
        protected String doInBackground(Void... voids) {
            try {
                URL url = new URL("http://192.168.201.3/QuicktableAPI/fetch_tablestatus.php");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");

                InputStream inputStream = conn.getInputStream();
                BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(inputStream));
                StringBuilder stringBuilder = new StringBuilder();
                String line;

                while ((line = bufferedReader.readLine()) != null) {
                    stringBuilder.append(line);
                }

                bufferedReader.close();
                inputStream.close();
                conn.disconnect();

                return stringBuilder.toString();
            } catch (Exception e) {
                e.printStackTrace();
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {
            if (result != null) {
                try {
                    JSONArray jsonArray = new JSONArray(result);
                    List<String> occupiedTables = new ArrayList<>();

                    for (int i = 0; i < jsonArray.length(); i++) {
                        JSONObject jsonObject = jsonArray.getJSONObject(i);
                        String tableNo = jsonObject.getString("table_no");
                        String status = jsonObject.getString("status");

                        if ("occupied".equals(status)) {
                            occupiedTables.add(tableNo);
                        }
                    }

                    updateButtonVisibility(occupiedTables);
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }
        }
    }

    private void updateButtonVisibility(List<String> occupiedTables) {
        for (Button button : buttonMap.values()) {
            button.setVisibility(View.GONE);
        }

        for (String table : occupiedTables) {
            Button button = buttonMap.get(table);
            if (button != null) {
                button.setVisibility(View.VISIBLE);
            }
        }

        rearrangeButtons(occupiedTables);
    }

    private void rearrangeButtons(List<String> occupiedTables) {
        gridLayout.removeAllViews();

        for (int i = 0; i < occupiedTables.size(); i += 2) {
            String table1 = occupiedTables.get(i);
            Button button1 = buttonMap.get(table1);
            gridLayout.addView(button1);

            if (i + 1 < occupiedTables.size()) {
                String table2 = occupiedTables.get(i + 1);
                Button button2 = buttonMap.get(table2);
                gridLayout.addView(button2);
            }
        }
    }

    public void openTableActivity(View view) {
        Button button = (Button) view;
        String tableName = button.getText().toString();

        Intent intent = new Intent(UpdateTableActivity.this, UpdateBtnActivity.class);
        intent.putExtra("TABLE_NAME", tableName);
        startActivity(intent);
    }
}
