pipeline {
    agent any
    stages {
        stage('Verify SSH connection to server') {
            steps {
                sshagent(credentials: ['aws-ec2']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no ec2-user@3.83.154.232 whoami
                    '''
                }
            }
        }
        stage('Populate .env file') {
            steps {
                dir('/var/lib/jenkins/workspace/envs/uniquo-test') {
                    fileOperations([fileCopyOperation(excludes: '', flattenFiles: true, includes: '.env', targetLocation: "${WORKSPACE}")])
                }
            }
        }
        stage('Create Code Zip') {
            steps {
                sh 'make create_code_zip'
            }
        }
        stage('Upload Code Zip to EC2') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: 'aws-ec2', keyFileVariable: 'keyfile')]) {
                    sh 'scp -v -o StrictHostKeyChecking=no -i ${keyfile} /var/lib/jenkins/workspace/UniquoTest/artifact.zip ec2-user@3.83.154.232:/home/ec2-user/artifact'
                }
            }
        }
        stage('Make project on EC2') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: 'aws-ec2', keyFileVariable: 'keyfile')]) {
                    sshagent(credentials: ['aws-ec2']) {
                        // SSH into the EC2 instance
                    //     sh '''
                    //     ssh -o StrictHostKeyChecking=no -i ${keyfile} ec2-user@3.83.154.232 << 'EOF'
                    //         sudo unzip -o ~/artifact/artifact.zip -d ~/projects/uniquo-test
                    //     EOF
                    // '''
                        sh '''
                        ssh -o StrictHostKeyChecking=no -i ${keyfile} ec2-user@3.83.154.232 << 'EOF'
                            sudo unzip -o ~/artifact/artifact.zip -d ~/projects/uniquo-test
                            cd ~/projects/uniquo-test
                            docker compose down
                            docker compose up -d --build
                            sudo chown -R $USER .
                            docker compose exec uniquo-app sh -c 'composer install && php artisan key:generate && php artisan migrate:refresh --seed'
                        EOF
                    '''
                    }
                }
            }
        }
    }
}
