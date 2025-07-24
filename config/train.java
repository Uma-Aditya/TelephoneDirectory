import java.util.Scanner;
class train{
    public static void main(String[] args) {
         
        Scanner sc = new Scanner(System.in);

        char str[] = sc.nextLine().toUpperCase().toCharArray();  // to char array returns a character array so , we need to provide a character array
        for(char ch : str){
            System.out.print(ch);
        }
        System.out.print(str);



    }
}

// there are total 4 quadrants
// each of those quadrants contains 3 months .
// 